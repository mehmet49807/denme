<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSettingsService
{
    private const CACHE_KEY = 'site_settings_all';

    /** @var array<string, string>|null */
    private static ?array $memory = null;

    public function defaults(): array
    {
        return [
            'site_name' => 'Gönül Köprüsü',
            'site_url' => rtrim(config('app.url', 'https://www.gonulkoprusu.com'), '/'),
            'default_description' => 'Gönül Köprüsü — Türkiye\'nin güvenli tanışma, sohbet ve evlilik sitesi. Ücretsiz üye ol, şehrine göre keşfet, ciddi ilişki kur.',
            'default_keywords' => 'gönül köprüsü, tanışma sitesi, ücretsiz tanışma sitesi, evlilik sitesi, online sohbet, sohbet sitesi, flört sitesi, ciddi ilişki, güvenli tanışma, online tanışma, eş bulma',
            'og_image_url' => rtrim(config('app.url', 'https://gonulkoprusu.com'), '/').'/images/logo-320.png',
            'twitter_handle' => '@gonulkoprusu',
            'support_email' => 'destek@gonulkoprusu.com',
            'support_phone' => '',
            'support_whatsapp' => '',
            'support_hours' => '7/24',
            'instagram_url' => 'https://www.instagram.com/gonulkoprusu',
            'facebook_url' => 'https://www.facebook.com/gonulkoprusu',
            'twitter_url' => 'https://www.twitter.com/gonulkoprusu',
            'google_analytics_id' => (string) config('services.google_analytics.id', 'G-7Z411GWG80'),
            'google_tag_manager_id' => (string) (config('services.google_tag_manager.id') ?: 'GTM-57LJQ8PP'),
            'google_site_verification' => (string) config('services.google_search_console.verification', ''),
            'bing_site_verification' => '',
            'robots_index' => '1',
            'sitemap_enabled' => '1',
            'android_app_url' => '',
            'ios_app_url' => '',
        ];
    }

    public function all(): array
    {
        if (self::$memory !== null) {
            return self::$memory;
        }

        try {
            self::$memory = Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function () {
                $this->ensureTableExists();

                if (! Schema::hasTable('site_settings')) {
                    return $this->defaults();
                }

                $stored = SiteSetting::query()->pluck('value', 'key')->all();
                $stored = array_filter($stored, fn ($value) => trim((string) $value) !== '');

                return array_merge($this->defaults(), $stored);
            });
        } catch (\Throwable) {
            self::$memory = $this->defaults();
        }

        return self::$memory;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        $value = $all[$key] ?? $default;

        if ($key === 'google_tag_manager_id') {
            return self::normalizeGtmId((string) $value);
        }

        return $value;
    }

    private static function normalizeGtmId(string $id): string
    {
        $id = strtoupper(trim($id));

        return match ($id) {
            '', 'GTM-57LJQ8P' => 'GTM-57LJQ8PP',
            default => $id,
        };
    }

    public function bool(string $key, bool $default = true): bool
    {
        $value = $this->get($key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function setMany(array $data): void
    {
        $this->ensureTableExists();

        if (! Schema::hasTable('site_settings')) {
            return;
        }

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            SiteSetting::query()->updateOrCreate(
                ['key' => (string) $key],
                ['value' => $value === null ? '' : (string) $value],
            );
        }

        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        self::$memory = null;

        try {
            Cache::forget(self::CACHE_KEY);
            Cache::forget('sitemap.xml.body');
        } catch (\Throwable) {
            //
        }
    }

    private function ensureTableExists(): void
    {
        if (Schema::hasTable('site_settings')) {
            return;
        }

        try {
            Schema::create('site_settings', function ($table) {
                $table->string('key', 100)->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        } catch (\Throwable) {
            //
        }
    }
}
