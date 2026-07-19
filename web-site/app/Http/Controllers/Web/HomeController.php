<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SeoHelper;
use App\Support\SeoSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
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
        ]);
    }
}
