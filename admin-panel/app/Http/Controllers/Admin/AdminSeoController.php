<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OpenRouterService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class AdminSeoController extends Controller
{
    public function index(SiteSettingsService $settings): View
    {
        $values = $settings->all();
        $frontendUrl = rtrim(config('app.frontend_url', 'https://gonulkoprusu.com'), '/');

        return view('admin.seo', [
            'settings' => $values,
            'frontendUrl' => $frontendUrl,
            'sitemapUrl' => $frontendUrl.'/sitemap.xml',
            'robotsUrl' => $frontendUrl.'/robots.txt',
            'searchUrl' => $frontendUrl.'/ara',
            'openRouterConfigured' => (string) config('services.openrouter.api_key') !== '',
            'openRouterModel' => (string) config('services.openrouter.model', 'openrouter/free'),
            'openRouterLastUpdated' => $this->openRouterLastUpdated(),
        ]);
    }

    public function update(Request $request, SiteSettingsService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:120',
            'site_url' => 'required|url|max:255',
            'default_description' => 'required|string|max:500',
            'default_keywords' => 'nullable|string|max:500',
            'og_image_url' => 'nullable|url|max:500',
            'twitter_handle' => 'nullable|string|max:80',
            'support_email' => 'nullable|email|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'google_analytics_id' => 'nullable|string|max:40|regex:/^(G-[A-Z0-9]+)?$/',
            'google_tag_manager_id' => 'nullable|string|max:40|regex:/^(GTM-[A-Z0-9]+)?$/',
            'google_site_verification' => 'nullable|string|max:120',
            'bing_site_verification' => 'nullable|string|max:120',
            'robots_index' => 'nullable|boolean',
            'sitemap_enabled' => 'nullable|boolean',
        ], [
            'site_name.required' => 'Site adı zorunludur.',
            'site_url.required' => 'Site adresi zorunludur.',
            'default_description.required' => 'Varsayılan açıklama zorunludur.',
            'google_analytics_id.regex' => 'Google Analytics ID G- ile başlamalıdır (ör. G-XXXXXXXX).',
            'google_tag_manager_id.regex' => 'Google Tag Manager ID GTM- ile başlamalıdır.',
        ]);

        $settings->setMany([
            'site_name' => trim($validated['site_name']),
            'site_url' => rtrim(trim($validated['site_url']), '/'),
            'default_description' => trim($validated['default_description']),
            'default_keywords' => trim((string) ($validated['default_keywords'] ?? '')),
            'og_image_url' => trim((string) ($validated['og_image_url'] ?? '')),
            'twitter_handle' => trim((string) ($validated['twitter_handle'] ?? '')),
            'support_email' => trim((string) ($validated['support_email'] ?? '')),
            'instagram_url' => trim((string) ($validated['instagram_url'] ?? '')),
            'facebook_url' => trim((string) ($validated['facebook_url'] ?? '')),
            'twitter_url' => trim((string) ($validated['twitter_url'] ?? '')),
            'google_analytics_id' => strtoupper(trim((string) ($validated['google_analytics_id'] ?? ''))),
            'google_tag_manager_id' => strtoupper(trim((string) ($validated['google_tag_manager_id'] ?? ''))),
            'google_site_verification' => trim((string) ($validated['google_site_verification'] ?? '')),
            'bing_site_verification' => trim((string) ($validated['bing_site_verification'] ?? '')),
            'robots_index' => $request->boolean('robots_index'),
            'sitemap_enabled' => $request->boolean('sitemap_enabled'),
        ]);

        return redirect()
            ->route('admin.seo')
            ->with('success', 'SEO ve Google arama ayarları kaydedildi.');
    }

    public function clearSitemapCache(SiteSettingsService $settings): RedirectResponse
    {
        $settings->forgetCache();

        return redirect()
            ->route('admin.seo')
            ->with('success', 'Sitemap önbelleği temizlendi.');
    }

    public function openRouterHelp(): RedirectResponse
    {
        return redirect()
            ->route('admin.seo')
            ->with('error', 'OpenRouter güncellemesini SEO sayfasındaki butondan çalıştırın.');
    }

    public function refreshOpenRouter(OpenRouterService $openRouter): RedirectResponse
    {
        try {
            $payload = $openRouter->chat(
                $this->openRouterSeoSystemPrompt(),
                $this->openRouterSeoUserPrompt(),
                1800,
            );
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.seo')
                ->with('error', 'OpenRouter bağlantı hatası: '.$e->getMessage());
        }

        if (! $payload) {
            return redirect()
                ->route('admin.seo')
                ->with('error', 'OpenRouter yanıt vermedi veya API anahtarı tanımlı değil.');
        }

        try {
            $payload['updated_at'] = now()->toDateString();
            Storage::put('seo/openrouter-weekly.json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.seo')
                ->with('error', 'OpenRouter çıktısı kaydedilemedi: '.$e->getMessage());
        }

        return redirect()
            ->route('admin.seo')
            ->with('success', 'OpenRouter haftalık Türkçe SEO önerileri güncellendi.');
    }

    private function openRouterLastUpdated(): ?string
    {
        if (! Storage::exists('seo/openrouter-weekly.json')) {
            return null;
        }

        $payload = json_decode((string) Storage::get('seo/openrouter-weekly.json'), true);

        return is_array($payload) ? ($payload['updated_at'] ?? null) : null;
    }

    private function openRouterSeoSystemPrompt(): string
    {
        return <<<'PROMPT'
Sen Gönül Köprüsü için çalışan Türkçe SEO içerik editörüsün.
Her zaman yalnızca Türkçe yaz.
Ton: güven veren, saygılı, aile ve ciddi ilişki odaklı, abartısız ve doğal.
Amaç: organik trafik için blog, SSS ve şehir bazlı sayfa fikirleri üretmek.
Yasaklar: İngilizce içerik yazma, tıbbi/hukuki kesin iddia yazma, rakip marka adı kullanma, spam anahtar kelime doldurma.
JSON formatında dön: {"updated_at":"YYYY-MM-DD","blog_ideas":[...],"faq_ideas":[...],"city_page_ideas":[...],"internal_links":[...]}.
PROMPT;
    }

    private function openRouterSeoUserPrompt(): string
    {
        return <<<'PROMPT'
Bu hafta Gönül Köprüsü için organik trafik büyütmeye yönelik içerik önerileri üret.
Odak konular:
- İstanbul evlilik sitesi, Ankara tanışma, İzmir ciddi ilişki gibi şehir niyetli aramalar
- Güvenli tanışma, ilk buluşma, profil ipuçları
- "Ciddi ilişki nasıl bulunur?" benzeri Blog / SSS soruları
Her blog fikrinde: başlık, slug, meta_description, hedef_anahtar_kelime, kısa_özet alanları olsun.
Her SSS fikrinde: soru ve kısa cevap olsun.
Her şehir sayfası fikrinde: şehir, hedef arama niyeti, başlık ve kısa içerik önerisi olsun.
PROMPT;
    }
}
