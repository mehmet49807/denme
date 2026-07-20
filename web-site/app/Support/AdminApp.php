<?php

namespace App\Support;

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
}
