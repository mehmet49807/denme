<?php

namespace App\Http\Middleware;

use App\Support\AdminApp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Setup endpoint erişimi: geçerli key VEYA admin alt alanında personel oturumu.
 * Deploy hook'ları key ile çalışmaya devam eder.
 */
class SetupAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $expectedKey = 'gk-cpanel-setup-2026'): Response
    {
        $provided = (string) $request->query('key', $request->input('key', ''));
        $keyOk = $provided !== '' && hash_equals($expectedKey, $provided);

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
