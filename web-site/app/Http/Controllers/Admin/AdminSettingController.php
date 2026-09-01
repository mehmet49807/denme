<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index(Request $request)
    {
        $settings = SiteSetting::orderBy('key')->get();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings'              => 'nullable|array',
            'settings.*.key'        => 'required|string|max:100',
            'settings.*.value'      => 'nullable|string|max:5000',
            'new_key'               => 'nullable|string|max:100',
            'new_value'             => 'nullable|string|max:5000',
        ]);

        if (! empty($validated['settings'])) {
            foreach ($validated['settings'] as $item) {
                SiteSetting::updateOrCreate(
                    ['key' => $item['key']],
                    ['value' => $item['value'] ?? '']
                );
            }
        }

        if (! empty($validated['new_key'])) {
            SiteSetting::updateOrCreate(
                ['key' => $validated['new_key']],
                ['value' => $validated['new_value'] ?? '']
            );
        }

        return back()->with('success', 'Site ayarları güncellendi.');
    }
}
