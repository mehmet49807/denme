<?php

namespace App\Services;

use App\Models\AiModerationFlag;
use App\Models\AiModerationReport;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AiModerationService
{
    public function __construct(
        private ContentPolicyService $policy,
        private OpenRouterService $openRouter,
        private NotificationService $notifications,
    ) {}

    public function validateOutgoingText(string $text, string $context = 'content'): void
    {
        $this->policy->validateTextOrFail($text, $context);
    }

    public function moderateMessage(Message $message): void
    {
        $message->loadMissing(['sender', 'receiver']);
        $text = (string) $message->message_text;
        $user = $message->sender;

        if (! $user || $user->role !== 'user') {
            return;
        }

        $regexHit = $this->policy->scanText($text);
        if ($regexHit) {
            $this->recordFlag($user, AiModerationFlag::TYPE_MESSAGE, $message->id, $regexHit, $text, 'regex');
            $this->hideMessage($message);
            $this->notifications->notifyModerationViolation($user, $regexHit['label'], AiModerationFlag::TYPE_MESSAGE);

            return;
        }

        $ai = $this->analyzeTextWithAi($text, 'private message in a dating app');

        if ($ai && ! empty($ai['violation'])) {
            $this->recordFlag($user, AiModerationFlag::TYPE_MESSAGE, $message->id, $ai, $text, 'ai');
            if (($ai['severity'] ?? 'medium') !== 'low') {
                $this->hideMessage($message);
                $this->notifications->notifyModerationViolation(
                    $user,
                    $ai['reason'] ?? 'Mesajınız güvenlik kurallarına aykırı.',
                    AiModerationFlag::TYPE_MESSAGE,
                );
            }
        }
    }

    public function moderatePost(Post $post): void
    {
        $post->loadMissing('user');
        $user = $post->user;
        $caption = (string) ($post->caption ?? '');

        if (! $user || $user->role !== 'user') {
            return;
        }

        if ($caption !== '') {
            $regexHit = $this->policy->scanText($caption);
            if ($regexHit) {
                $this->recordFlag($user, AiModerationFlag::TYPE_POST, $post->id, $regexHit, $caption, 'regex');
                $this->deactivatePost($post);
                $this->notifications->notifyModerationViolation($user, $regexHit['label'], AiModerationFlag::TYPE_POST);

                return;
            }

            $ai = $this->analyzeTextWithAi($caption, 'post caption on a dating app feed');

            if ($ai && ! empty($ai['violation'])) {
                $this->recordFlag($user, AiModerationFlag::TYPE_POST, $post->id, $ai, $caption, 'ai');
                if (($ai['severity'] ?? 'medium') !== 'low') {
                    $this->deactivatePost($post);
                    $this->notifications->notifyModerationViolation(
                        $user,
                        $ai['reason'] ?? 'Gönderiniz güvenlik kurallarına aykırı.',
                        AiModerationFlag::TYPE_POST,
                    );
                }
            }
        }
    }

    public function moderateStory(Story $story): void
    {
        $story->loadMissing('user');
        $user = $story->user;

        if (! $user || $user->role !== 'user') {
            return;
        }

        $ai = $this->analyzeTextWithAi(
            'User uploaded a '.$story->media_type.' story. Check for off-platform contact attempts in metadata context.',
            'story media upload — flag if likely contains phone/iban/social redirect',
        );

        if ($ai && ! empty($ai['violation']) && ($ai['severity'] ?? 'medium') === 'high') {
            $this->recordFlag($user, AiModerationFlag::TYPE_STORY, $story->id, $ai, 'Hikaye medyası', 'ai');
        }
    }

    public function moderateReport(Report $report): void
    {
        $report->loadMissing(['reporter', 'reported']);
        $reported = $report->reported;

        if (! $reported || $reported->role !== 'user') {
            return;
        }

        $recentMessages = Message::query()
            ->where(function ($q) use ($report) {
                $q->where('sender_id', $report->reported_id)->where('receiver_id', $report->reporter_id);
            })
            ->orWhere(function ($q) use ($report) {
                $q->where('sender_id', $report->reporter_id)->where('receiver_id', $report->reported_id);
            })
            ->latest('created_at')
            ->limit(10)
            ->pluck('message_text')
            ->implode("\n---\n");

        $prompt = "Şikayet nedeni:\n{$report->reason}\n\nSon mesajlar:\n".($recentMessages ?: '(mesaj yok)');

        $ai = $this->analyzeTextWithAi($prompt, 'user complaint review for dating platform admin');

        if (! $ai) {
            return;
        }

        $summary = $ai['reason'] ?? 'AI şikayet analizi tamamlandı.';
        $severity = $ai['severity'] ?? 'medium';

        AiModerationReport::create([
            'report_type' => AiModerationReport::TYPE_COMPLAINT,
            'title' => 'Şikayet #'.$report->id.' · '.($report->reported->username ?? 'kullanıcı'),
            'summary' => $summary,
            'details' => [
                'report_id' => $report->id,
                'reporter_id' => $report->reporter_id,
                'reported_id' => $report->reported_id,
                'ai' => $ai,
            ],
            'status' => 'published',
        ]);

        if (! empty($ai['violation'])) {
            $this->recordFlag($reported, AiModerationFlag::TYPE_REPORT, $report->id, $ai, $report->reason, 'ai');
        }

        if (($ai['categories'] ?? []) && in_array('fake_profile', (array) ($ai['categories'] ?? []), true)) {
            $this->moderateUserProfile($reported, 'report_context');
        }
    }

    public function moderateUserProfile(User $user, string $context = 'profile'): void
    {
        if ($user->role !== 'user') {
            return;
        }

        $profileText = implode("\n", array_filter([
            'username: '.$user->username,
            'name: '.$user->first_name.' '.$user->last_name,
            'city: '.$user->city,
            'country: '.($user->country ?? ''),
            'phone: '.($user->phone ?? ''),
        ]));

        $regexHit = $this->policy->scanText($profileText);
        if ($regexHit) {
            $this->recordFlag($user, AiModerationFlag::TYPE_PROFILE, $user->id, $regexHit, $profileText, 'regex');

            return;
        }

        $ai = $this->analyzeTextWithAi(
            $profileText,
            'dating app user profile — detect fake/scam profiles, inconsistent names, spam patterns',
        );

        if ($ai && ! empty($ai['violation'])) {
            $categories = (array) ($ai['categories'] ?? []);
            if (in_array('fake_profile', $categories, true) || in_array('fraud', $categories, true)) {
                $this->recordFlag($user, AiModerationFlag::TYPE_PROFILE, $user->id, $ai, $profileText, 'ai');
            }
        }
    }

    public function generateDailyReport(): AiModerationReport
    {
        $since = now()->subDay();
        $flags = AiModerationFlag::where('created_at', '>=', $since)->get();
        $pendingReports = Report::where('status', 'pending')->count();

        $byCategory = $flags->groupBy('category')->map->count()->all();
        $byType = $flags->groupBy('content_type')->map->count()->all();

        $summary = sprintf(
            'Son 24 saatte %d ihlal tespit edildi. Bekleyen şikayet: %d.',
            $flags->count(),
            $pendingReports,
        );

        $aiSummary = null;
        if ($this->openRouter->isConfigured() && $flags->isNotEmpty()) {
            $aiSummary = $this->openRouter->chat(
                'Sen bir moderasyon analistisin. Türkçe JSON döndür: {"summary":"...","recommendations":["..."]}',
                'Son 24 saat ihlaller: '.json_encode(['categories' => $byCategory, 'types' => $byType, 'total' => $flags->count()], JSON_UNESCAPED_UNICODE),
                500,
            );
        }

        return AiModerationReport::create([
            'report_type' => AiModerationReport::TYPE_DAILY,
            'title' => 'Günlük AI Denetim Raporu · '.now()->format('d.m.Y'),
            'summary' => is_array($aiSummary) ? ($aiSummary['summary'] ?? $summary) : $summary,
            'details' => [
                'flags_total' => $flags->count(),
                'by_category' => $byCategory,
                'by_type' => $byType,
                'pending_reports' => $pendingReports,
                'ai_recommendations' => is_array($aiSummary) ? ($aiSummary['recommendations'] ?? []) : [],
            ],
            'status' => 'published',
        ]);
    }

    /** @return array<string, mixed>|null */
    private function analyzeTextWithAi(string $text, string $context): ?array
    {
        if (! $this->openRouter->isConfigured() || trim($text) === '') {
            return null;
        }

        try {
            $result = $this->openRouter->chat(
                <<<'PROMPT'
Sen Gönül Köprüsü tanışma platformu moderasyon AI'sısın. Türkçe içerikleri analiz et.
JSON döndür:
{
  "violation": true/false,
  "categories": ["iban","money_request","phone","social_media","fraud","fake_profile","other"],
  "severity": "low|medium|high",
  "reason": "kısa Türkçe açıklama",
  "confidence": 0.0-1.0
}
Kurallar: IBAN/para talebi/dış platform yönlendirme/telefon paylaşımı/dolandırıcılık/sahte profil ihlal sayılır.
PROMPT,
                "Bağlam: {$context}\n\nİçerik:\n".$text,
                350,
            );

            if (! is_array($result)) {
                return null;
            }

            return [
                'violation' => (bool) ($result['violation'] ?? false),
                'categories' => (array) ($result['categories'] ?? []),
                'category' => (string) (($result['categories'][0] ?? null) ?: AiModerationFlag::CATEGORY_OTHER),
                'severity' => (string) ($result['severity'] ?? 'medium'),
                'reason' => (string) ($result['reason'] ?? 'AI ihlal tespiti'),
                'confidence' => (float) ($result['confidence'] ?? 0.5),
                'label' => (string) ($result['reason'] ?? 'AI ihlal tespiti'),
            ];
        } catch (\Throwable $e) {
            Log::warning('AI moderation failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /** @param array<string, mixed> $hit */
    private function recordFlag(
        User $user,
        string $contentType,
        ?int $contentId,
        array $hit,
        string $rawText,
        string $source,
    ): void {
        $category = (string) ($hit['category'] ?? AiModerationFlag::CATEGORY_OTHER);
        if (isset($hit['categories'][0]) && is_string($hit['categories'][0])) {
            $category = $hit['categories'][0];
        }

        $exists = AiModerationFlag::query()
            ->where('user_id', $user->id)
            ->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('category', $category)
            ->where('created_at', '>=', now()->subHours(6))
            ->exists();

        if ($exists) {
            return;
        }

        AiModerationFlag::create([
            'user_id' => $user->id,
            'content_type' => $contentType,
            'content_id' => $contentId,
            'category' => $category,
            'severity' => (string) ($hit['severity'] ?? 'medium'),
            'source' => $source,
            'status' => AiModerationFlag::STATUS_PENDING,
            'content_excerpt' => $this->policy->excerpt($rawText),
            'ai_reason' => (string) ($hit['reason'] ?? $hit['label'] ?? null),
            'ai_confidence' => isset($hit['confidence']) ? (float) $hit['confidence'] : null,
        ]);
    }

    private function hideMessage(Message $message): void
    {
        $message->forceFill([
            'hidden_for_sender_at' => now(),
            'hidden_for_receiver_at' => now(),
        ])->saveQuietly();
    }

    private function deactivatePost(Post $post): void
    {
        $post->forceFill(['is_active' => false])->saveQuietly();
    }
}

