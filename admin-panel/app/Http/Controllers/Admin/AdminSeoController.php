<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSeoController extends Controller
{
    public function index(SiteSettingsService $settings): View
    {
        $values = $settings->all();
        $frontendUrl = rtrim(config('app.frontend_url', 'https://gonulkoprusu.com'), '/');

        return view('admin.seo', [
            'settings' => $values,
            'frontendUrl' => $frontendUrl,
            'sitemapUrl' => $frontendUrl.'/sitemap.xml',
            'robotsUrl' => $frontendUrl.'/robots.txt',
            'searchUrl' => $frontendUrl.'/ara',
        ]);
    }

    public function update(Request $request, SiteSettingsService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:120',
            'site_url' => 'required|url|max:255',
            'default_description' => 'required|string|max:500',
            'default_keywords' => 'nullable|string|max:500',
            'og_image_url' => 'nullable|url|max:500',
            'twitter_handle' => 'nullable|string|max:80',
            'support_email' => 'nullable|email|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'google_analytics_id' => 'nullable|string|max:40|regex:/^(G-[A-Z0-9]+)?$/',
            'google_tag_manager_id' => 'nullable|string|max:40|regex:/^(GTM-[A-Z0-9]+)?$/',
            'google_site_verification' => 'nullable|string|max:120',
            'bing_site_verification' => 'nullable|string|max:120',
            'robots_index' => 'nullable|boolean',
            'sitemap_enabled' => 'nullable|boolean',
        ], [
            'site_name.required' => 'Site adı zorunludur.',
            'site_url.required' => 'Site adresi zorunludur.',
            'default_description.required' => 'Varsayılan açıklama zorunludur.',
            'google_analytics_id.regex' => 'Google Analytics ID G- ile başlamalıdır (ör. G-XXXXXXXX).',
            'google_tag_manager_id.regex' => 'Google Tag Manager ID GTM- ile başlamalıdır.',
        ]);

        $settings->setMany([
            'site_name' => trim($validated['site_name']),
            'site_url' => rtrim(trim($validated['site_url']), '/'),
            'default_description' => trim($validated['default_description']),
            'default_keywords' => trim((string) ($validated['default_keywords'] ?? '')),
            'og_image_url' => trim((string) ($validated['og_image_url'] ?? '')),
            'twitter_handle' => trim((string) ($validated['twitter_handle'] ?? '')),
            'support_email' => trim((string) ($validated['support_email'] ?? '')),
            'instagram_url' => trim((string) ($validated['instagram_url'] ?? '')),
            'facebook_url' => trim((string) ($validated['facebook_url'] ?? '')),
            'twitter_url' => trim((string) ($validated['twitter_url'] ?? '')),
            'google_analytics_id' => strtoupper(trim((string) ($validated['google_analytics_id'] ?? ''))),
            'google_tag_manager_id' => strtoupper(trim((string) ($validated['google_tag_manager_id'] ?? ''))),
            'google_site_verification' => trim((string) ($validated['google_site_verification'] ?? '')),
            'bing_site_verification' => trim((string) ($validated['bing_site_verification'] ?? '')),
            'robots_index' => $request->boolean('robots_index'),
            'sitemap_enabled' => $request->boolean('sitemap_enabled'),
        ]);

        return redirect()
            ->route('admin.seo')
            ->with('success', 'SEO ve Google arama ayarları kaydedildi.');
    }

    public function clearSitemapCache(SiteSettingsService $settings): RedirectResponse
    {
        $settings->forgetCache();

        return redirect()
            ->route('admin.seo')
            ->with('success', 'Sitemap önbelleği temizlendi.');
    }
}
