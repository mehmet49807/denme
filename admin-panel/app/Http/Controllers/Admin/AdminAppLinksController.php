<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAppLinksController extends Controller
{
    public function index(SiteSettingsService $settings): View
    {
        return view('admin.app-links', [
            'androidAppUrl' => (string) $settings->get('android_app_url', ''),
            'iosAppUrl' => (string) $settings->get('ios_app_url', ''),
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
