<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index(Request $request)
    {
        $settings = app(SiteSettingsService::class)->all();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name'              => 'nullable|string|max:100',
            'support_email'          => 'nullable|string|max:200',
            'maintenance_mode'       => 'nullable|in:0,1',
            'registration_enabled'   => 'nullable|in:0,1',
            'require_photo_approval' => 'nullable|in:0,1',
            'free_message_limit'     => 'nullable|integer|min:0|max:1000',
            'min_age'                => 'nullable|integer|min:15|max:99',
            'site_description'       => 'nullable|string|max:5000',
            'default_description'    => 'nullable|string|max:5000',
            'default_keywords'       => 'nullable|string|max:2000',
            'support_phone'          => 'nullable|string|max:50',
            'support_whatsapp'       => 'nullable|string|max:50',
            'support_hours'          => 'nullable|string|max:50',
            'instagram_url'           => 'nullable|string|max:500',
            'facebook_url'            => 'nullable|string|max:500',
            'twitter_url'             => 'nullable|string|max:500',
            'company_name'            => 'nullable|string|max:200',
            'company_tax_office'      => 'nullable|string|max:200',
            'company_tax_number'      => 'nullable|string|max:50',
            'company_mersis'          => 'nullable|string|max:50',
            'company_trade_registry'  => 'nullable|string|max:200',
            'company_address'          => 'nullable|string|max:500',
            'company_phone'            => 'nullable|string|max:50',
            'company_email'            => 'nullable|string|max:200',
            'company_representative'   => 'nullable|string|max:200',
            'company_kvkk_contact'     => 'nullable|string|max:200',
            'google_analytics_id'      => 'nullable|string|max:50',
            'google_tag_manager_id'    => 'nullable|string|max:50',
        ]);

        // Convert empty strings to '' and booleans to '0'/'1'
        $data = [];
        foreach ($validated as $key => $value) {
            if ($value === null) {
                continue;
            }
            $data[$key] = (string) $value;
        }

        // Ensure checkbox values default to '0' if not sent
        if (!isset($data['maintenance_mode'])) {
            $data['maintenance_mode'] = '0';
        }
        if (!isset($data['registration_enabled'])) {
            $data['registration_enabled'] = '1';
        }
        if (!isset($data['require_photo_approval'])) {
            $data['require_photo_approval'] = '1';
        }

        app(SiteSettingsService::class)->setMany($data);

        return back()->with('success', 'Site ayarları güncellendi.');
    }
}
