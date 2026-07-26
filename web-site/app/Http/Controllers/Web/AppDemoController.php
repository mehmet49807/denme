<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SetupKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppDemoController extends Controller
{
    public const DEMO_KEY = 'gk-app-demo-2026';

    public const SESSION_FLAG = 'gk_app_demo';

    public const COOKIE_FLAG = 'gk_app_demo';

    public function show(Request $request): Response|RedirectResponse
    {
        if (! $this->authorized($request)) {
            abort(403, 'Demo linki geçersiz. Admin → Uygulama sayfasındaki özel linki kullanın.');
        }

        $this->activateDemo($request);

        if ($request->boolean('go')) {
            $target = auth()->check()
                ? route('feed')
                : route('login', ['redirect' => route('feed')]);

            return redirect()
                ->to($target)
                ->cookie(self::COOKIE_FLAG, '1', 60 * 24 * 14, '/', null, true, false, false, 'Lax');
        }

        return response()
            ->view('web.app-demo', [
                'demoKey' => self::DEMO_KEY,
                'openUrl' => route('app.demo', ['key' => self::DEMO_KEY, 'go' => 1]),
                'feedUrl' => route('feed'),
                'loginUrl' => route('login', ['redirect' => route('feed')]),
                'isAuthed' => auth()->check(),
            ])
            ->cookie(self::COOKIE_FLAG, '1', 60 * 24 * 14, '/', null, true, false, false, 'Lax');
    }

    public function exit(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_FLAG);

        return redirect()
            ->route('home')
            ->withCookie(cookie()->forget(self::COOKIE_FLAG));
    }

    public static function isActive(?Request $request = null): bool
    {
        $request = $request ?? request();

        if ((string) $request->session()->get(self::SESSION_FLAG) === '1') {
            return true;
        }

        return (string) $request->cookie(self::COOKIE_FLAG) === '1';
    }

    private function authorized(Request $request): bool
    {
        $key = (string) $request->query('key', '');

        return SetupKey::matches($key, self::DEMO_KEY);
    }

    private function activateDemo(Request $request): void
    {
        $request->session()->put(self::SESSION_FLAG, '1');
    }
}
