<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;

class AdminApp
{
    public static function isSubdomainRequest(): bool
    {
        if (filter_var(env('ADMIN_SUBDOMAIN', false), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        $host = strtolower((string) request()->getHost());
        $adminHost = strtolower((string) parse_url((string) config('app.admin_url'), PHP_URL_HOST));

        return $adminHost !== '' && $host === $adminHost;
    }

    public static function loginPath(): string
    {
        return self::isSubdomainRequest() ? '/login' : '/adminlogin/login';
    }

    public static function userIsStaff(?object $user): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isStaff')) {
            return (bool) $user->isStaff();
        }

        return method_exists($user, 'isAdmin') && (bool) $user->isAdmin();
    }

    public static function requestHasSharedRememberCookie(Request $request): bool
    {
        foreach (array_keys($request->cookies->all()) as $name) {
            if (is_string($name) && str_starts_with($name, 'remember_web_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ana site (gonulkoprusu.com) ile paylaşılan remember/session kimliğini temizle.
     * SESSION_DOMAIN=.gonulkoprusu.com iken remember_web_* admin'e de gelir.
     */
    public static function purgeNonStaffAuth(Request $request): void
    {
        try {
            if (Auth::check()) {
                Auth::logout();
            }
        } catch (\Throwable) {
            //
        }

        try {
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        } catch (\Throwable) {
            //
        }

        self::expireSharedAuthCookies($request);
    }

    public static function expireSharedAuthCookies(Request $request): void
    {
        $names = [];

        foreach (array_keys($request->cookies->all()) as $name) {
            if (! is_string($name)) {
                continue;
            }

            if (str_starts_with($name, 'remember_web_') || $name === 'gonul_koprusu_session') {
                $names[] = $name;
            }
        }

        $domains = array_values(array_unique(array_filter([
            null,
            '.gonulkoprusu.com',
            'gonulkoprusu.com',
            'admin.gonulkoprusu.com',
            $request->getHost(),
        ])));

        foreach ($names as $name) {
            foreach ($domains as $domain) {
                cookie()->queue(new Cookie(
                    $name,
                    '',
                    1,
                    '/',
                    $domain,
                    true,
                    true,
                    false,
                    Cookie::SAMESITE_LAX
                ));
            }
        }
    }

    /**
     * Admin çerezlerini ana siteyle paylaşmayı kes (host-only).
     * Route dosyası yüklenirken çağrılır — StartSession'dan önce config set edilir.
     */
    public static function isolateSessionCookieDomain(): void
    {
        if (! self::isSubdomainRequest()) {
            return;
        }

        config([
            'session.domain' => null,
        ]);
    }
}
