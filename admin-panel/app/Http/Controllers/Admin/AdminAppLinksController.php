<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAppLinksController extends Controller
{
    /** Keep in sync with web-site AppDemoController::DEMO_KEY */
    private const APP_DEMO_KEY = 'gk-app-demo-2026';

    public function index(SiteSettingsService $settings): View
    {
        $frontend = rtrim((string) config('app.frontend_url', 'https://gonulkoprusu.com'), '/');

        return view('admin.app-links', [
            'androidAppUrl' => (string) $settings->get('android_app_url', ''),
            'iosAppUrl' => (string) $settings->get('ios_app_url', ''),
            'frontendUrl' => $frontend,
            'appDemoUrl' => $frontend.'/uygulama-demo?key='.self::APP_DEMO_KEY,
            'appDemoOpenUrl' => $frontend.'/uygulama-demo?key='.self::APP_DEMO_KEY.'&go=1',
        ]);
    }

    public function update(Request $request, SiteSettingsService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'android_app_url' => 'nullable|url|max:500',
            'ios_app_url' => 'nullable|url|max:500',
        ], [
            'android_app_url.url' => 'Android linki geçerli bir URL olmalıdır.',
            'ios_app_url.url' => 'iOS linki geçerli bir URL olmalıdır.',
        ]);

        $settings->setMany([
            'android_app_url' => trim((string) ($validated['android_app_url'] ?? '')),
            'ios_app_url' => trim((string) ($validated['ios_app_url'] ?? '')),
        ]);

        return redirect()
            ->route('admin.app-links')
            ->with('success', 'Android ve iOS uygulama linkleri kaydedildi. Boş bırakılan mağaza rozetleri “Yakında” gösterir.');
    }
}
