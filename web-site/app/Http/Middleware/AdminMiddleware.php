<?php

namespace App\Http\Middleware;

use App\Support\AdminApp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $allowed = $user && (
            method_exists($user, 'isStaff')
                ? $user->isStaff()
                : $user->isAdmin()
        );

        if (! $allowed) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu işlem için yönetici yetkisi gereklidir.',
                ], 403);
            }

            return redirect(AdminApp::loginPath())
                ->withErrors(['login' => 'Yönetici yetkisi gereklidir.']);
        }

        // One-time schema widen for staff roles (enum user/admin → varchar).
        try {
            if (class_exists(\App\Services\AdminAuditService::class)) {
                app(\App\Services\AdminAuditService::class)->ensureStaffRoleColumn();
            }
        } catch (\Throwable) {
            //
        }

        return $next($request);
    }
}
