<?php

namespace App\Http\Middleware;

use App\Support\AdminApp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * guest yerine: yalnızca gerçek personel oturumu login sayfasından dashboard'a gider.
 * Ana siteden gelen remember_web_* çerezi döngüye sokulmaz.
 */
class RedirectIfAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && AdminApp::userIsStaff($user)) {
            return redirect()->route('admin.dashboard');
        }

        if ($user || AdminApp::requestHasSharedRememberCookie($request)) {
            AdminApp::purgeNonStaffAuth($request);
        }

        return $next($request);
    }
}
