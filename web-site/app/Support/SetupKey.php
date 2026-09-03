<?php

namespace App\Support;

/**
 * Setup / deploy hook authentication.
 * Production accepts only a non-weak SETUP_CACHE_KEY from the environment.
 */
class SetupKey
{
    /** @var list<string> */
    public const WEAK_DEFAULTS = [
        'gk-cpanel-setup-2026',
        'gk-deploy-sync-2026',
        'gk-delete-users-2026',
        'gk-laravel-update-2026',
        'gk-seo-sync-2026',
        'gk-perf-setup-2026',
        'gk-notif-setup-2026',
    ];

    public static function configured(): string
    {
        return trim((string) env('SETUP_CACHE_KEY', ''));
    }

    public static function matches(?string $provided, ?string $legacyFallback = null): bool
    {
        $expected = self::configured();
        $provided = trim((string) $provided);

        if ($expected === '' || $provided === '') {
            return false;
        }

        if (app()->environment('production') && self::isWeak($expected)) {
            return false;
        }

        if (hash_equals($expected, $provided)) {
            return true;
        }

        // Legacy route arguments are retained for signature compatibility only;
        // never accept publicly known fallbacks in production.
        if (! app()->environment('production')) {
            $legacy = trim((string) $legacyFallback);
            return $legacy !== '' && hash_equals($legacy, $provided);
        }

        return false;
    }

    public static function seoMatches(?string $provided): bool
    {
        $expected = trim((string) config('services.seo.sync_key', ''));
        $provided = trim((string) $provided);

        if ($expected === '' || $provided === '') {
            return false;
        }

        return hash_equals($expected, $provided);
    }

    public static function isWeak(?string $key): bool
    {
        return in_array(trim((string) $key), self::WEAK_DEFAULTS, true);
    }
}
