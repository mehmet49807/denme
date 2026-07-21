<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ApplyUserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if ($request->user() && ! empty($request->user()->locale)) {
            $locale = (string) $request->user()->locale;
        } elseif ($request->cookie('gk_locale')) {
            $locale = (string) $request->cookie('gk_locale');
        } elseif ($request->session()->has('locale')) {
            $locale = (string) $request->session()->get('locale');
        }

        if ($locale && in_array($locale, ['tr', 'en', 'de', 'fr', 'hi'], true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
