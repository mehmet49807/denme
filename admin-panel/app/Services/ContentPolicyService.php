<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class ContentPolicyService
{
    /** @var list<array{category: string, pattern: string, label: string}> */
    private array $rules = [
        ['category' => 'iban', 'label' => 'IBAN paylaşımı', 'pattern' => '/\bTR[\s\d]{10,32}\b/iu'],
        ['category' => 'iban', 'label' => 'IBAN paylaşımı', 'pattern' => '/\biban\b[\s:]*[\d\s]{10,}/iu'],
        ['category' => 'phone', 'label' => 'Telefon numarası', 'pattern' => '/(?:\+90|0)?[\s\-]?5[\d\s\-]{8,12}\d/iu'],
        ['category' => 'phone', 'label' => 'Telefon numarası', 'pattern' => '/\b0[\s\-]?5\d{2}[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}\b/iu'],
        ['category' => 'social_media', 'label' => 'Sosyal medya', 'pattern' => '/\b(?:instagram|insta|whatsapp|wp|telegram|t\.me|snapchat|tiktok|facebook|twitter|x\.com|snap)\b/iu'],
        ['category' => 'social_media', 'label' => 'Sosyal medya', 'pattern' => '/@[a-z0-9_.]{3,30}\b/iu'],
        ['category' => 'social_media', 'label' => 'Sosyal medya', 'pattern' => '/\b(?:wa\.me|t\.me|ig\.me)\/\S+/iu'],
        ['category' => 'money_request', 'label' => 'Para talebi', 'pattern' => '/\b(?:para\s*(?:gönder|at|iste|yolla)|havale|eft|papara|kripto|bitcoin|usdt|dolar\s*ist|lira\s*ist)\b/iu'],
        ['category' => 'money_request', 'label' => 'Para talebi', 'pattern' => '/\b(?:iban\s*(?:at|ver|gönder)|hesap\s*no)\b/iu'],
        ['category' => 'fraud', 'label' => 'Dolandırıcılık', 'pattern' => '/\b(?:yatırım\s*fırsat|garanti\s*kazanç|acil\s*para|western\s*union)\b/iu'],
    ];

    /** @return array<string, string> */
    public function configurableCategories(): array
    {
        return [
            'iban' => 'IBAN paylaşımı',
            'phone' => 'Telefon numarası',
            'social_media' => 'Sosyal medya yönlendirme',
            'money_request' => 'Para talebi',
            'fraud' => 'Dolandırıcılık',
        ];
    }

    /** @return array<string, bool> */
    public function enabledCategories(?SiteSettingsService $settings = null): array
    {
        $settings ??= app(SiteSettingsService::class);
        $enabled = [];

        foreach ($this->configurableCategories() as $key => $label) {
            $enabled[$key] = $settings->bool('content_policy_'.$key, true);
        }

        return $enabled;
    }

    /** @return list<array{category: string, pattern: string, label: string}> */
    private function activeRules(?SiteSettingsService $settings = null): array
    {
        $enabled = $this->enabledCategories($settings);
        $rules = array_values(array_filter(
            $this->rules,
            fn (array $rule) => ($enabled[$rule['category']] ?? true) === true,
        ));

        $settings ??= app(SiteSettingsService::class);
        $custom = trim((string) $settings->get('content_policy_custom_patterns', ''));
        if ($custom !== '') {
            foreach (preg_split('/\R+/', $custom) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                $pattern = '/'.str_replace('/', '\/', $line).'/iu';
                if (@preg_match($pattern, '') === false) {
                    continue;
                }
                $rules[] = [
                    'category' => 'custom',
                    'label' => 'Özel kural',
                    'pattern' => $pattern,
                ];
            }
        }

        return $rules;
    }

    /** @return array{category: string, label: string, reason: string, severity: string}|null */
    public function scanText(string $text): ?array
    {
        $normalized = trim($text);
        if ($normalized === '') {
            return null;
        }

        foreach ($this->activeRules() as $rule) {
            if (preg_match($rule['pattern'], $normalized)) {
                return [
                    'category' => $rule['category'],
                    'label' => $rule['label'],
                    'reason' => $rule['label'].' tespit edildi.',
                    'severity' => in_array($rule['category'], ['iban', 'money_request', 'fraud'], true) ? 'high' : 'medium',
                ];
            }
        }

        return null;
    }

    public function validateTextOrFail(string $text, string $context = 'content'): void
    {
        $hit = $this->scanText($text);

        if ($hit === null) {
            return;
        }

        $message = match ($context) {
            'message' => 'Mesajınız güvenlik kurallarına aykırı: '.$hit['label'].'. IBAN, telefon, sosyal medya ve para talepleri yasaktır.',
            'post' => 'Gönderi metniniz güvenlik kurallarına aykırı: '.$hit['label'].'.',
            'story' => 'Hikaye içeriği güvenlik kurallarına aykırı: '.$hit['label'].'.',
            default => 'İçerik güvenlik kurallarına aykırı: '.$hit['label'].'.',
        };

        throw ValidationException::withMessages([
            $context === 'message' ? 'message_text' : 'caption' => $message,
        ]);
    }

    public function excerpt(string $text, int $limit = 240): string
    {
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';

        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit).'…' : $text;
    }
}
