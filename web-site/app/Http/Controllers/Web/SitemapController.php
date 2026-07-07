<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    /**
     * Dinamik sitemap.xml olustur
     */
    public function index()
    {
        $settings = app(\App\Services\SiteSettingsService::class);
        if (! $settings->bool('sitemap_enabled', true)) {
            abort(404);
        }

        $xml = Cache::remember('sitemap.xml.body', now()->addHour(), function () use ($settings) {
            return $this->buildSitemapXml($settings);
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=3600')
            ->header('X-Robots-Tag', 'noindex');
    }

    private function buildSitemapXml(?\App\Services\SiteSettingsService $settings = null): string
    {
        $settings ??= app(\App\Services\SiteSettingsService::class);
        $baseUrl = rtrim((string) $settings->get('site_url', 'https://www.gonulkoprusu.com'), '/');
        $urls = [];

        // ========== 1. Statik Sayfalar ==========
        $staticPages = [
            ['loc' => '/',                      'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/hakkimizda',            'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/blog',                  'priority' => '0.85', 'changefreq' => 'weekly'],
            ['loc' => '/sss',                   'priority' => '0.85', 'changefreq' => 'weekly'],
            ['loc' => '/destek',                'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/register',              'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => '/gizlilik-politikasi',   'priority' => '0.5', 'changefreq' => 'yearly'],
            ['loc' => '/kvkk',                  'priority' => '0.5', 'changefreq' => 'yearly'],
            ['loc' => '/kullanim-kosullari',     'priority' => '0.5', 'changefreq' => 'yearly'],
            ['loc' => '/sikayet-ve-engelleme',  'priority' => '0.5', 'changefreq' => 'yearly'],
            ['loc' => '/guvenli-tanisma',       'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/ara',                   'priority' => '0.6', 'changefreq' => 'daily'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc'        => $baseUrl . $page['loc'],
                'lastmod'    => now()->toDateString(),
                'changefreq' => $page['changefreq'],
                'priority'   => $page['priority'],
            ];
        }

        foreach ($this->publishedBlogPosts() as $post) {
            $slug = (string) ($post['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $urls[] = [
                'loc'        => $baseUrl.'/blog/'.$slug,
                'lastmod'    => (string) ($post['updated_at'] ?? now()->toDateString()),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ];
        }

        // ========== 2. Kullanici Profil Sayfalari ==========
        $users = User::where('is_banned', false)
            ->whereNotNull('username')
            ->where('role', 'user')
            ->select('username', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(5000)
            ->get();

        foreach ($users as $user) {
            $urls[] = [
                'loc'        => $baseUrl . '/users/' . $user->username,
                'lastmod'    => $user->updated_at->toDateString(),
                'changefreq' => 'weekly',
                'priority'   => '0.6',
            ];
        }

        // ========== 3. Sehir/Konum Sayfalari ==========
        $locations = app(\App\Services\LocationDataService::class);
        foreach ($locations->seoCitySlugs('Türkiye') as $citySlug) {
            $urls[] = [
                'loc'        => $baseUrl . '/sehir/' . $citySlug,
                'lastmod'    => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority'   => '0.75',
            ];
        }

        $cities = DB::table('users')
            ->where('is_banned', false)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->select('country', 'city')
            ->distinct()
            ->get();

        foreach ($cities as $city) {
            $country = $city->country ?: 'turkiye';
            $citySlug = mb_strtolower(str_replace(' ', '-', $city->city));
            $countrySlug = mb_strtolower(str_replace(' ', '-', $country));

            $urls[] = [
                'loc'        => $baseUrl . '/locations/' . $countrySlug . '/' . $citySlug,
                'lastmod'    => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ];
        }

        // ========== Desteklenen Diller ==========
        $languages = ['tr', 'en', 'de', 'fr', 'hi'];

        // ========== XML Olustur ==========
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml"' . "\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";

            // Hreflang etiketleri (Cok dilli SEO)
            foreach ($languages as $lang) {
                $langUrl = $lang === 'tr' ? $url['loc'] : $url['loc'] . '?lang=' . $lang;
                $xml .= '    <xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . htmlspecialchars($langUrl) . '" />' . "\n";
            }
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($url['loc']) . '" />' . "\n";

            $xml .= '  </url>' . "\n";
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
