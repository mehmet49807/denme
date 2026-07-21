<?php

namespace App\Http\Middleware;

use App\Services\UserAttributionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureGrowthAttribution
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->hasAttributionParams($request)) {
            app(UserAttributionService::class)->captureFromRequest($request);
        }

        return $next($request);
    }

    private function hasAttributionParams(Request $request): bool
    {
        foreach (['ref', 'utm_source', 'utm_medium', 'utm_campaign'] as $key) {
            if (filled($request->query($key)) || filled($request->input($key))) {
                return true;
            }
        }

        return false;
    }
}
