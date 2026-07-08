<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunAiModerationJob;
use App\Models\AiModerationFlag;
use App\Models\User;
use App\Services\AiModerationService;
use App\Services\OpenRouterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AdminAiController extends Controller
{
    public function index(OpenRouterService $openRouter): View
    {
        $flags = AiModerationFlag::with('user')
            ->latest()
            ->paginate(25);

        $stats = [
            'pending' => AiModerationFlag::where('status', AiModerationFlag::STATUS_PENDING)->count(),
            'today' => AiModerationFlag::where('created_at', '>=', now()->startOfDay())->count(),
            'high' => AiModerationFlag::where('severity', 'high')->where('status', AiModerationFlag::STATUS_PENDING)->count(),
            'ai_source' => AiModerationFlag::where('source', 'ai')->where('created_at', '>=', now()->subDay())->count(),
        ];

        $connection = $openRouter->testConnection();

        return view('admin.ai', [
            'flags' => $flags,
            'stats' => $stats,
            'connection' => $connection,
            'model' => $openRouter->model(),
            'configured' => $openRouter->isConfigured(),
        ]);
    }

    public function scan(): RedirectResponse
    {
        Artisan::call('ai:scan-pending', ['--hours' => 48]);

        return redirect()->route('admin.ai')->with('success', 'AI tarama başlatıldı. Sonuçlar birkaç dakika içinde listelenir.');
    }

    public function testConnection(OpenRouterService $openRouter): RedirectResponse
    {
        $result = $openRouter->testConnection();

        return redirect()->route('admin.ai')->with(
            $result['ok'] ? 'success' : 'error',
            $result['message'],
        );
    }

    public function publishBlogFaq(OpenRouterService $openRouter): RedirectResponse
    {
        if (! $openRouter->isConfigured()) {
            return redirect()
                ->route('admin.ai')
                ->with('error', 'OpenRouter API anahtarı tanımlı değil. SEO ayarlarından API anahtarını kontrol edin.');
        }

        try {
            $payload = $openRouter->chat(
                $this->blogFaqSystemPrompt(),
                $this->blogFaqUserPrompt(),
                2800,
            );
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.ai')
                ->with('error', 'OpenRouter Blog / SSS üretim hatası: '.$e->getMessage());
        }

        if (! $payload) {
            return redirect()
                ->route('admin.ai')
                ->with('error', 'OpenRouter Blog / SSS için geçerli JSON yanıtı üretmedi. Bağlantı testini çalıştırıp tekrar deneyin.');
        }

        $published = $this->normalizeBlogFaqPayload($payload);

        if (empty($published['blog_posts']) && empty($published['faq_items'])) {
            return redirect()
                ->route('admin.ai')
                ->with('error', 'OpenRouter yanıtında yayınlanabilir Blog veya SSS içeriği bulunamadı.');
        }

        $json = json_encode($published, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        try {
            Storage::put('seo/openrouter-published-blog-faq.json', $json);
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.ai')
                ->with('error', 'Blog / SSS içeriği kaydedilemedi: '.$e->getMessage());
        }

        $syncWarnings = $this->syncPublishedBlogFaqToWebsite($published, $json);

        $message = count($published['blog_posts']).' blog yazısı ve '.count($published['faq_items']).' SSS sorusu yayınlandı.';

        if ($syncWarnings !== []) {
            $message .= ' Uyarı: '.implode(' ', $syncWarnings);
        }

        return redirect()
            ->route('admin.ai')
            ->with($syncWarnings === [] ? 'success' : 'error', $message);
    }

    public function updateFlag(Request $request, AiModerationFlag $flag): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,actioned,dismissed',
            'admin_notes' => 'nullable|string|max:2000',
            'ban_user' => 'nullable|boolean',
        ]);

        $flag->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        if ($request->boolean('ban_user')) {
            $user = $flag->user;
            if ($user && $user->role === 'user') {
                $user->update([
                    'is_banned' => true,
                    'banned_at' => now(),
                    'banned_reason' => 'AI denetim: '.$flag->categoryLabel(),
                ]);
            }
        }

        return redirect()->route('admin.ai')->with('success', 'İhlal kaydı güncellendi.');
    }

    public function scanProfile(User $user, AiModerationService $moderation): RedirectResponse
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        RunAiModerationJob::dispatchAfterResponse('profile', $user->id);

        return redirect()->route('admin.ai')->with('success', $user->username.' profili AI taramasına alındı.');
    }

    private function blogFaqSystemPrompt(): string
    {
        return <<<'PROMPT'
Sen Gönül Köprüsü için çalışan Türkçe SEO editörüsün.
Her zaman yalnızca Türkçe yaz.
Ton: güven veren, saygılı, aile ve ciddi ilişki odaklı, doğal ve abartısız.
Amaç: organik trafik için yayına hazır Blog ve SSS içeriği üretmek.
Yasaklar: İngilizce yazma, spam anahtar kelime doldurma, rakip marka adı kullanma, tıbbi/hukuki kesin vaat verme.
Yalnızca JSON döndür.
JSON şeması:
{
  "blog_posts": [
    {
      "slug": "kisa-url-slug",
      "title": "Başlık",
      "description": "150 karakter civarı meta açıklama",
      "reading_time": "5 dk",
      "keywords": ["anahtar kelime"],
      "sections": [
        {"heading": "Alt başlık", "body": "En az 2 cümlelik paragraf"}
      ],
      "faq": [
        {"question": "Soru?", "answer": "Kısa cevap"}
      ]
    }
  ],
  "faq_items": [
    {"question": "Soru?", "answer": "Kısa cevap"}
  ],
  "internal_links": [
    {"label": "Bağlantı metni", "url": "/blog"}
  ]
}
PROMPT;
    }

    private function blogFaqUserPrompt(): string
    {
        return <<<'PROMPT'
Gönül Köprüsü için bugün yayına alınacak içerikleri üret:
- 2 adet tam blog yazısı üret.
- Her blog yazısında 4 bölüm olsun.
- Her blog yazısında 2 kısa SSS olsun.
- 8 adet genel SSS sorusu üret.
Odak konular:
- Ciddi ilişki nasıl bulunur?
- İstanbul evlilik sitesi, Ankara tanışma, İzmir ciddi ilişki
- Güvenli tanışma, ilk buluşma, profil fotoğrafı ve profil ipuçları
- KVKK, moderasyon, şikayet/engelleme ve güven
Slug değerleri Türkçe karakter içermesin, küçük harf ve tireli olsun.
PROMPT;
    }

    private function normalizeBlogFaqPayload(array $payload): array
    {
        if (isset($payload['raw_content']) && is_string($payload['raw_content']) && trim($payload['raw_content']) !== '') {
            $rawContent = trim($payload['raw_content']);

            return [
                'published_at' => now()->toIso8601String(),
                'blog_posts' => [[
                    'slug' => 'openrouter-turkce-blog-sss-'.now()->format('YmdHis'),
                    'title' => 'OpenRouter Türkçe Blog ve SSS Güncellemesi',
                    'description' => 'OpenRouter ile üretilen yeni Türkçe blog ve SSS içeriği.',
                    'updated_at' => now()->toDateString(),
                    'reading_time' => '5 dk',
                    'keywords' => ['ciddi ilişki', 'güvenli tanışma', 'evlilik sitesi'],
                    'sections' => [[
                        'heading' => 'Yeni Türkçe içerik',
                        'body' => Str::limit($rawContent, 5000, ''),
                    ]],
                    'faq' => [],
                ]],
                'faq_items' => [[
                    'question' => 'Gönül Köprüsü yeni içerikleri nasıl günceller?',
                    'answer' => 'Yönetim panelindeki AI Görevler ekranından OpenRouter ile Türkçe Blog ve SSS içeriği üretilebilir.',
                ]],
                'internal_links' => [],
            ];
        }

        $rawPosts = $payload['blog_posts'] ?? $payload['posts'] ?? $payload['blogs'] ?? [];
        $rawFaqItems = $payload['faq_items'] ?? $payload['faqs'] ?? [];

        $posts = collect(is_array($rawPosts) ? $rawPosts : [])
            ->take(4)
            ->map(function ($post) {
                if (! is_array($post)) {
                    return null;
                }

                $title = trim((string) ($post['title'] ?? $post['başlık'] ?? ''));
                if ($title === '') {
                    return null;
                }

                $sections = collect(is_array($post['sections'] ?? null) ? $post['sections'] : [])
                    ->take(6)
                    ->map(function ($section) {
                        if (! is_array($section)) {
                            return null;
                        }

                        $heading = trim((string) ($section['heading'] ?? $section['başlık'] ?? ''));
                        $body = trim((string) ($section['body'] ?? $section['metin'] ?? ''));

                        return $heading !== '' && $body !== ''
                            ? ['heading' => $heading, 'body' => $body]
                            : null;
                    })
                    ->filter()
                    ->values()
                    ->all();

                if (empty($sections)) {
                    $summary = trim((string) ($post['summary'] ?? $post['kısa_özet'] ?? $post['description'] ?? ''));
                    $sections = [['heading' => $title, 'body' => $summary !== '' ? $summary : $title]];
                }

                return [
                    'slug' => Str::slug((string) ($post['slug'] ?? $title)),
                    'title' => $title,
                    'description' => trim((string) ($post['description'] ?? $post['meta_description'] ?? $title)),
                    'updated_at' => now()->toDateString(),
                    'reading_time' => trim((string) ($post['reading_time'] ?? '5 dk')),
                    'keywords' => array_values(array_filter((array) ($post['keywords'] ?? []))),
                    'sections' => $sections,
                    'faq' => $this->normalizeFaqItems((array) ($post['faq'] ?? []), 3),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'published_at' => now()->toIso8601String(),
            'blog_posts' => $posts,
            'faq_items' => $this->normalizeFaqItems(is_array($rawFaqItems) ? $rawFaqItems : [], 12),
            'internal_links' => is_array($payload['internal_links'] ?? null) ? array_values($payload['internal_links']) : [],
        ];
    }

    private function normalizeFaqItems(array $items, int $limit): array
    {
        return collect($items)
            ->take($limit)
            ->map(function ($item) {
                if (! is_array($item)) {
                    return null;
                }

                $question = trim((string) ($item['question'] ?? $item['soru'] ?? ''));
                $answer = trim((string) ($item['answer'] ?? $item['cevap'] ?? ''));

                return $question !== '' && $answer !== ''
                    ? ['question' => $question, 'answer' => $answer]
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $published */
    private function syncPublishedBlogFaqToWebsite(array $published, string $json): array
    {
        $warnings = [];
        $syncedToWeb = false;

        foreach ($this->websiteStorageCandidates() as $webStoragePath) {
            try {
                File::ensureDirectoryExists(dirname($webStoragePath));
                File::put($webStoragePath, $json);
                $syncedToWeb = true;
                break;
            } catch (Throwable $e) {
                Log::warning('Blog / SSS dosya senkronu başarısız', [
                    'path' => $webStoragePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $frontendUrl = rtrim((string) config('app.frontend_url', 'https://gonulkoprusu.com'), '/');
        $syncKey = (string) config('services.seo.sync_key', env('SEO_SYNC_KEY', 'gk-seo-sync-2026'));

        try {
            $response = Http::timeout(45)
                ->retry(1, 1500)
                ->asJson()
                ->post($frontendUrl.'/setup/seo-blog-faq-sync', [
                    'key' => $syncKey,
                    'payload' => $published,
                ]);

            if ($response->successful() && data_get($response->json(), 'ok') === true) {
                $syncedToWeb = true;
            } elseif (! $syncedToWeb) {
                $warnings[] = 'Web sitesi HTTP senkronu başarısız: '.($response->json('message') ?? $response->body());
            }
        } catch (Throwable $e) {
            if (! $syncedToWeb) {
                $warnings[] = 'Web sitesine aktarım yapılamadı: '.$e->getMessage();
            }

            Log::warning('Blog / SSS HTTP senkronu başarısız', ['error' => $e->getMessage()]);
        }

        return $warnings;
    }

    /** @return list<string> */
    private function websiteStorageCandidates(): array
    {
        return array_values(array_unique(array_filter([
            base_path('../public_html/storage/app/seo/openrouter-published-blog-faq.json'),
            base_path('storage/app/seo/openrouter-published-blog-faq.json'),
            (string) config('services.seo.frontend_storage_path'),
        ])));
    }
}
