<?php

namespace App\Support;

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

    public static function matches(?string $provided): bool
    {
        $expected = self::configured();
        $provided = trim((string) $provided);

        if ($expected === '' || $provided === '') {
            return false;
        }

        if (app()->environment('production') && in_array($expected, self::WEAK_DEFAULTS, true)) {
            return false;
        }

        return hash_equals($expected, $provided);
    }
}
