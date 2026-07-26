<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\UserAttributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppAuthController extends Controller
{
    public function loginForm(Request $request): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('feed');
        }

        // Keep app-demo cookie/session alive when landing from demo
        if ($request->boolean('app') || AppDemoController::isActive($request)) {
            $request->session()->put(AppDemoController::SESSION_FLAG, '1');
        }

        app(UserAttributionService::class)->captureFromRequest($request);

        return view('web.app-login', [
            'redirect' => (string) $request->query('redirect', route('feed')),
        ]);
    }

    public function registerForm(Request $request): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('feed');
        }

        if ($request->boolean('app') || AppDemoController::isActive($request)) {
            $request->session()->put(AppDemoController::SESSION_FLAG, '1');
        }

        app(UserAttributionService::class)->captureFromRequest($request);

        return view('web.app-register');
    }
}
