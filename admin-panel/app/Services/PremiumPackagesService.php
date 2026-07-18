<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PremiumPackagesService
{
    public const SETTING_KEY = 'premium_packages_json';

    private const CACHE_KEY = 'premium_packages_catalog';

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $memory = null;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function defaults(): array
    {
        return [
            'pro' => [
                'name' => 'Pro',
                'duration_days' => 7,
                'price_tl' => 250,
                'badge_label' => 'Pro',
                'badge_enabled' => true,
                'badge_icon' => 'star',
                'rozet_label' => 'Pro Yıldız',
                'rozet_text' => 'Mor yıldız rozeti — profilinde “Pro” olarak görünür.',
                'gradient_from' => '#5b21b6',
                'gradient_to' => '#a78bfa',
                'featured' => false,
            ],
            'gold' => [
                'name' => 'Gold',
                'duration_days' => 14,
                'price_tl' => 400,
                'badge_label' => 'Gold',
                'badge_enabled' => true,
                'badge_icon' => 'crown',
                'rozet_label' => 'Gold Taç',
                'rozet_text' => 'Altın taç rozeti — öne çıkan ve popüler profil görünümü.',
                'gradient_from' => '#92400e',
                'gradient_to' => '#fbbf24',
                'featured' => true,
            ],
            'platinum' => [
                'name' => 'Platinum',
                'duration_days' => 30,
                'price_tl' => 500,
                'badge_label' => 'Platinum',
                'badge_enabled' => true,
                'badge_icon' => 'sparkles',
                'rozet_label' => 'Platinum Işıltı',
                'rozet_text' => 'Gümüş ışıltı rozeti — en prestijli ve en yüksek görünürlük.',
                'gradient_from' => '#0f172a',
                'gradient_to' => '#94a3b8',
                'featured' => false,
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function catalog(): array
    {
        if (self::$memory !== null) {
            return self::$memory;
        }

        try {
            self::$memory = Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function () {
                $settings = app(SiteSettingsService::class);
                $raw = (string) $settings->get(self::SETTING_KEY, '');
                $stored = $raw !== '' ? json_decode($raw, true) : [];

                if (! is_array($stored)) {
                    $stored = [];
                }

                return $this->mergeCatalog($stored);
            });
        } catch (\Throwable) {
            self::$memory = $this->mergeCatalog([]);
        }

        return self::$memory;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function legacyCatalog(): array
    {
        $catalog = [];

        foreach ($this->catalog() as $type => $package) {
            $catalog[$type] = [
                'name' => (string) ($package['name'] ?? ucfirst($type)),
                'duration_days' => (int) ($package['duration_days'] ?? 7),
                'price_tl' => (float) ($package['price_tl'] ?? 0),
            ];
        }

        return $catalog ?: [
            'pro' => ['name' => 'Pro', 'duration_days' => 7, 'price_tl' => 250],
            'gold' => ['name' => 'Gold', 'duration_days' => 14, 'price_tl' => 400],
            'platinum' => ['name' => 'Platinum', 'duration_days' => 30, 'price_tl' => 500],
        ];
    }

    public function package(string $type): ?array
    {
        $catalog = $this->catalog();

        return $catalog[$type] ?? null;
    }

    public function featuredType(): string
    {
        foreach ($this->catalog() as $type => $package) {
            if (! empty($package['featured'])) {
                return (string) $type;
            }
        }

        return 'gold';
    }

    public function badgeForUser(?User $user): ?array
    {
        if (! $user || $user->gender !== 'male' || ! $user->isPremium()) {
            return null;
        }

        $type = $user->activePackageType();
        if (! $type) {
            return null;
        }

        $package = $this->package($type);
        if (! $package || empty($package['badge_enabled'])) {
            return null;
        }

        return array_merge($package, ['type' => $type]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $packages
     */
    public function save(array $packages): void
    {
        $normalized = [];

        foreach ($this->defaults() as $type => $defaults) {
            $input = $packages[$type] ?? [];
            $normalized[$type] = $this->normalizePackage($type, $input, $defaults);
        }

        $featured = collect($normalized)->search(fn (array $pkg) => ! empty($pkg['featured']));
        if ($featured === false) {
            $normalized['gold']['featured'] = true;
        } else {
            foreach ($normalized as $type => $package) {
                $normalized[$type]['featured'] = $type === $featured;
            }
        }

        app(SiteSettingsService::class)->setMany([
            self::SETTING_KEY => json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        self::$memory = null;

        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable) {
            //
        }
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, array<string, mixed>>
     */
    private function mergeCatalog(array $stored): array
    {
        $catalog = [];

        foreach ($this->defaults() as $type => $defaults) {
            $catalog[$type] = $this->upgradeBadgeKit(
                $type,
                $this->normalizePackage(
                    $type,
                    is_array($stored[$type] ?? null) ? $stored[$type] : [],
                    $defaults,
                ),
                $defaults,
            );
        }

        if (! collect($catalog)->contains(fn (array $pkg) => ! empty($pkg['featured']))) {
            $catalog['gold']['featured'] = true;
        }

        return $catalog;
    }

    /**
     * @param  array<string, mixed>  $package
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    private function upgradeBadgeKit(string $type, array $package, array $defaults): array
    {
        $legacyRozet = [
            'pro' => ['Pro Rozet', 'Pro'],
            'gold' => ['Gold Rozet', 'Gold'],
            'platinum' => ['Platinum Rozet', 'Platinum'],
        ];

        $rozet = (string) ($package['rozet_label'] ?? '');
        $icon = (string) ($package['badge_icon'] ?? '');
        $to = strtolower((string) ($package['gradient_to'] ?? ''));
        $needsUpgrade = in_array($rozet, $legacyRozet[$type] ?? [], true)
            || ($type === 'platinum' && ($icon === 'bolt' || $to === '#f472b6'));

        if (! $needsUpgrade) {
            return $package;
        }

        foreach (['badge_label', 'badge_icon', 'rozet_label', 'rozet_text', 'gradient_from', 'gradient_to'] as $key) {
            $package[$key] = $defaults[$key];
        }

        return $package;
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    private function normalizePackage(string $type, array $input, array $defaults): array
    {
        $icons = ['star', 'crown', 'bolt', 'heart', 'sparkles'];
        $icon = (string) ($input['badge_icon'] ?? $defaults['badge_icon']);
        if (! in_array($icon, $icons, true)) {
            $icon = (string) $defaults['badge_icon'];
        }

        return [
            'name' => trim((string) ($input['name'] ?? $defaults['name'])) ?: (string) $defaults['name'],
            'duration_days' => max(1, (int) ($input['duration_days'] ?? $defaults['duration_days'])),
            'price_tl' => max(0, (float) ($input['price_tl'] ?? $defaults['price_tl'])),
            'badge_label' => trim((string) ($input['badge_label'] ?? $defaults['badge_label'])) ?: (string) $defaults['badge_label'],
            'badge_enabled' => filter_var($input['badge_enabled'] ?? $defaults['badge_enabled'], FILTER_VALIDATE_BOOL),
            'badge_icon' => $icon,
            'rozet_label' => trim((string) ($input['rozet_label'] ?? $defaults['rozet_label'])) ?: (string) $defaults['rozet_label'],
            'rozet_text' => trim((string) ($input['rozet_text'] ?? $defaults['rozet_text'])) ?: (string) $defaults['rozet_text'],
            'gradient_from' => $this->normalizeColor((string) ($input['gradient_from'] ?? $defaults['gradient_from']), (string) $defaults['gradient_from']),
            'gradient_to' => $this->normalizeColor((string) ($input['gradient_to'] ?? $defaults['gradient_to']), (string) $defaults['gradient_to']),
            'featured' => filter_var($input['featured'] ?? $defaults['featured'], FILTER_VALIDATE_BOOL),
            'type' => $type,
        ];
    }

    private function normalizeColor(string $value, string $fallback): string
    {
        $value = trim($value);

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? strtolower($value) : strtolower($fallback);
    }
}
