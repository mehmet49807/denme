<?php

namespace App\Http\Middleware;

use App\Support\AdminApp;
use App\Support\SetupKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Setup endpoint erişimi: geçerli key VEYA admin alt alanında personel oturumu.
 * Deploy hook'ları SETUP_CACHE_KEY (veya legacy route key) ile çalışmaya devam eder.
 */
class SetupAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $expectedKey = ''): Response
    {
        $provided = (string) $request->query('key', $request->input('key', ''));
        $keyOk = SetupKey::matches($provided, $expectedKey !== '' ? $expectedKey : null);

        $staffOk = false;
        if (AdminApp::isSubdomainRequest()) {
            $user = $request->user();
            $staffOk = AdminApp::userIsStaff($user);
        }

        if (! $keyOk && ! $staffOk) {
            abort(403);
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
