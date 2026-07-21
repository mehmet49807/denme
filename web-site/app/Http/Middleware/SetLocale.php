<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) ($request->cookie('gk_locale') ?: $request->session()->get('locale', ''));
        if ($locale !== '' && in_array($locale, ['tr', 'en', 'de', 'fr', 'hi'], true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
