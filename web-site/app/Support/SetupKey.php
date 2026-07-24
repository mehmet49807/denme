<?php

namespace App\Support;

/**
 * Setup / deploy hook authentication.
 * Accepts SETUP_CACHE_KEY from .env and optional legacy route fallbacks
 * so existing deploy hooks keep working while dangerous endpoints are removed.
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
        $provided = trim((string) $provided);
        if ($provided === '') {
            return false;
        }

        $env = self::configured();
        if ($env !== '' && hash_equals($env, $provided)) {
            return true;
        }

        $legacy = trim((string) $legacyFallback);
        if ($legacy !== '' && hash_equals($legacy, $provided)) {
            return true;
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
