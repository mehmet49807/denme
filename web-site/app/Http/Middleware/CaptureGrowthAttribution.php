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
        app(UserAttributionService::class)->captureFromRequest($request);

        return $next($request);
    }
}
