<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LocationDataService;
use App\Support\FeaturedCities;
use App\Support\SeoHelper;
use App\Support\SeoSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(LocationDataService $locations): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('feed');
        }

        SeoHelper::setPage('home');
        SeoHelper::set('pageType', 'home');
        SeoHelper::set('canonical', 'https://gonulkoprusu.com/');
        SeoHelper::set('ogImage', 'https://gonulkoprusu.com/images/logo-320.png');

        $siteUrl = 'https://gonulkoprusu.com';
        $description = (string) SeoHelper::get('description');

        $homeFaqs = [
            [
                'question' => 'Gönül Köprüsü ücretsiz tanışma sitesi mi?',
                'answer' => 'Evet. Üyelik ücretsizdir. Kadın üyelerde mesajlaşma ve kimler baktı ücretsizdir; erkekler deneme süresi ve isteğe bağlı premium ile devam eder.',
            ],
            [
                'question' => 'Evlilik ve ciddi ilişki için uygun mu?',
                'answer' => 'Platform ciddi ilişki ve evlilik niyetiyle tasarlandı. Moderasyon, engelleme ve güvenli tanışma rehberi ile saygılı bir ortam sunulur.',
            ],
            [
                'question' => 'Hangi şehirlerde tanışabilirim?',
                'answer' => 'Türkiye geneli 80+ şehir desteklenir. İstanbul, Ankara, İzmir ve diğer iller için özel tanışma sayfalarımız vardır.',
            ],
        ];

        $homeStories = [
            [
                'names' => 'Ayşe & Mehmet',
                'city' => 'İstanbul',
                'quote' => 'Ciddi niyet arıyorduk; burada buluştuk.',
            ],
            [
                'names' => 'Elif & Can',
                'city' => 'Ankara',
                'quote' => 'Başkentte sakin ve saygılı bir ortam.',
            ],
            [
                'names' => 'Zeynep & Emre',
                'city' => 'İzmir',
                'quote' => 'Flört değil, gerçek bağ istedik.',
            ],
        ];

        $stats = Cache::remember('home.member_stats.v1', 120, function () {
            try {
                $base = User::query()
                    ->where('role', 'user')
                    ->where('is_banned', false);

                $memberCount = (clone $base)->count();
                $onlineCount = (clone $base)
                    ->whereNotNull('last_active_at')
                    ->where('last_active_at', '>=', now()->subMinutes(User::ONLINE_MINUTES))
                    ->count();

                return [
                    'member_count' => $memberCount,
                    'online_count' => $onlineCount,
                ];
            } catch (\Throwable) {
                return [
                    'member_count' => 0,
                    'online_count' => 0,
                ];
            }
        });

        $heroCities = array_slice(FeaturedCities::links($locations), 0, 8);

        $faqGraph = SeoSchema::faqPage($homeFaqs);
        $graph = [
            [
                '@type' => 'WebSite',
                'name' => 'Gönül Köprüsü',
                'url' => $siteUrl,
                'description' => $description,
                'inLanguage' => 'tr-TR',
                'potentialAction' => [
                    '@type' => 'RegisterAction',
                    'target' => $siteUrl.'/register',
                    'name' => 'Ücretsiz üye ol',
                ],
            ],
            [
                '@type' => 'Organization',
                'name' => 'Gönül Köprüsü',
                'url' => $siteUrl,
                'logo' => $siteUrl.'/images/logo-320.png',
                'sameAs' => [
                    'https://www.instagram.com/gonulkoprusucom',
                ],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'email' => 'destek@gonulkoprusu.com',
                    'contactType' => 'customer support',
                    'availableLanguage' => ['Turkish'],
                ],
            ],
            SeoSchema::webApplication($siteUrl, $description),
        ];

        foreach ($faqGraph['@graph'] ?? [] as $node) {
            if (($node['@type'] ?? '') === 'FAQPage') {
                $graph[] = $node;
            }
        }

        return view('web.home', [
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@graph' => $graph,
            ],
            'homeFaqs' => $homeFaqs,
            'homeStories' => $homeStories,
            'memberCount' => (int) ($stats['member_count'] ?? 0),
            'onlineCount' => (int) ($stats['online_count'] ?? 0),
            'heroCities' => $heroCities,
        ]);
    }
}
