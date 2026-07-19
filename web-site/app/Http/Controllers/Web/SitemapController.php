<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Dinamik sitemap.xml olustur — yalnızca herkese açık SEO sayfaları.
     */
    public function index()
    {
        $settings = app(\App\Services\SiteSettingsService::class);
        if (! $settings->bool('sitemap_enabled', true)) {
            abort(404);
        }

        $xml = Cache::remember('sitemap.xml.body.v3', now()->addHour(), function () use ($settings) {
            return $this->buildSitemapXml($settings);
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=3600');
    }

    private function buildSitemapXml(?\App\Services\SiteSettingsService $settings = null): string
    {
        $settings ??= app(\App\Services\SiteSettingsService::class);
        $baseUrl = rtrim((string) $settings->get('site_url', 'https://gonulkoprusu.com'), '/');
        $urls = [];

        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/evlilik-sitesi', 'priority' => '0.98', 'changefreq' => 'weekly'],
            ['loc' => '/ciddi-iliski', 'priority' => '0.98', 'changefreq' => 'weekly'],
            ['loc' => '/ucretsiz-tanisma-sitesi', 'priority' => '0.98', 'changefreq' => 'weekly'],
            ['loc' => '/arkadaslik-sitesi', 'priority' => '0.95', 'changefreq' => 'weekly'],
            ['loc' => '/hakkimizda', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/blog', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/sss', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/register', 'priority' => '0.95', 'changefreq' => 'monthly'],
            ['loc' => '/gizlilik-politikasi', 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => '/kvkk', 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => '/kullanim-kosullari', 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => '/sikayet-ve-engelleme', 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => '/guvenli-tanisma', 'priority' => '0.85', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $baseUrl.$page['loc'],
                'lastmod' => now()->toDateString(),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        foreach ($this->publishedBlogPosts() as $post) {
            $slug = (string) ($post['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $urls[] = [
                'loc' => $baseUrl.'/blog/'.$slug,
                'lastmod' => (string) ($post['updated_at'] ?? now()->toDateString()),
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ];
        }

        $locations = app(\App\Services\LocationDataService::class);
        foreach ($locations->seoCitySlugs('Türkiye') as $citySlug) {
            $urls[] = [
                'loc' => $baseUrl.'/sehir/'.$citySlug,
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc']).'</loc>'."\n";
            $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
            $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="tr" href="'.htmlspecialchars($url['loc']).'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.htmlspecialchars($url['loc']).'" />'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /** @return array<int, array<string, mixed>> */
    private function publishedBlogPosts(): array
    {
        foreach ([
            storage_path('app/seo/openrouter-published-blog-faq.json'),
            base_path('storage/app/seo/openrouter-published-blog-faq.json'),
            base_path('../public_html/storage/app/seo/openrouter-published-blog-faq.json'),
        ] as $path) {
            if (! is_file($path)) {
                continue;
            }
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded) && is_array($decoded['blog_posts'] ?? null)) {
                return $decoded['blog_posts'];
            }
        }

        return [];
    }
}
