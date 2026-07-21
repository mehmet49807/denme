<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    private const THROTTLE_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if (! $user) {
            return $response;
        }

        // Poll / sync endpoints should not force a write every few seconds.
        $path = trim($request->path(), '/');
        if (
            str_starts_with($path, 'live/sync')
            || str_starts_with($path, 'notifications/badge-counts')
            || str_starts_with($path, 'notifications/poll')
            || str_starts_with($path, 'messages/inbox/poll')
            || str_contains($path, '/poll')
            || str_contains($path, '/typing')
        ) {
            return $response;
        }

        $cacheKey = 'last_active:'.$user->id;

        try {
            if (! Cache::add($cacheKey, 1, self::THROTTLE_SECONDS)) {
                return $response;
            }
        } catch (\Throwable) {
            // Cache yoksa yine de seyrek güncelle.
        }

        try {
            $user->forceFill(['last_active_at' => now()])->saveQuietly();
        } catch (\Throwable) {
            //
        }

        return $response;
    }
}
