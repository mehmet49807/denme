<?php

namespace App\Support;

use App\Services\SiteSettingsService;

final class InstagramUrl
{
    public const DEFAULT = 'https://www.instagram.com/gonulkoprusucom/';

    public static function base(): string
    {
        try {
            $url = trim((string) app(SiteSettingsService::class)->get('instagram_url', self::DEFAULT));
        } catch (\Throwable) {
            $url = self::DEFAULT;
        }

        $normalized = rtrim($url, '/');
        if ($normalized === '' || preg_match('#instagram\.com/gonulkoprusu/?$#i', $normalized)) {
            // Eski varsayılan /gonulkoprusu → canlı hesap @gonulkoprusucom
            $url = self::DEFAULT;
        }

        return rtrim($url, '/').'/';
    }

    public static function withUtm(string $source, string $medium = 'site', string $campaign = 'instagram'): string
    {
        $base = self::base();
        $sep = str_contains($base, '?') ? '&' : '?';

        return $base.$sep.http_build_query([
            'utm_source' => $source,
            'utm_medium' => $medium,
            'utm_campaign' => $campaign,
        ]);
    }
}
