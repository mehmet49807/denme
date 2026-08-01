<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\UserAttributionService;
use App\Support\FeaturedCities;
use App\Support\InstagramUrl;
use App\Support\SeoHelper;
use App\Services\LocationDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Meta / Google Ads ve Instagram kampanya landing — UTM yakalar, tek CTA.
 */
class CampaignLandingController extends Controller
{
    public function show(Request $request, LocationDataService $locations): View
    {
        app(UserAttributionService::class)->captureFromRequest($request);

        $source = strtolower((string) $request->query('utm_source', $request->query('src', 'ads')));
        $medium = strtolower((string) $request->query('utm_medium', 'paid'));
        $campaign = strtolower((string) $request->query('utm_campaign', 'growth'));
        $citySlug = strtolower((string) $request->query('city', ''));

        $cityName = null;
        if ($citySlug !== '') {
            $cityName = $locations->resolveCitySlug($citySlug);
        }

        $copy = $this->campaignCopy($campaign, $cityName);

        SeoHelper::set('title', $copy['title']);
        SeoHelper::set('description', $copy['description']);
        SeoHelper::set('robots', 'noindex,follow');

        $registerParams = [
            'utm_source' => $source !== '' ? $source : 'ads',
            'utm_medium' => $medium !== '' ? $medium : 'paid',
            'utm_campaign' => $campaign !== '' ? $campaign : 'growth',
        ];
        if ($citySlug !== '') {
            $registerParams['city'] = $citySlug;
        }

        return view('web.campaign-landing', [
            'cityName' => $cityName,
            'citySlug' => $citySlug,
            'headline' => $copy['headline'],
            'lead' => $copy['lead'],
            'registerUrl' => route('register', $registerParams),
            'googleUrl' => url('auth/google'),
            'instagramUrl' => InstagramUrl::withUtm($source ?: 'ads', $medium ?: 'paid', $campaign ?: 'growth'),
            'cityLinks' => FeaturedCities::links($locations),
            'source' => $source,
            'medium' => $medium,
            'campaign' => $campaign,
        ]);
    }

    /**
     * @return array{title: string, description: string, headline: string, lead: string}
     */
    private function campaignCopy(string $campaign, ?string $cityName): array
    {
        $cityPrefix = $cityName ? $cityName.' tanışma — ' : '';
        $defaults = [
            'title' => 'Ücretsiz Kayıt Ol — Gönül Köprüsü Tanışma',
            'description' => $cityPrefix.'Gönül Köprüsü\'ne ücretsiz üye ol. Güvenli sohbet, ciddi ilişki ve evlilik odaklı platform.',
            'headline' => $cityName ? $cityName.' tanışma — ücretsiz üye ol' : 'Ücretsiz üye ol, güvenle tanış',
            'lead' => 'Ciddi ilişki ve evlilik odaklı Gönül Köprüsü. Kadın üyelerde mesajlaşma ücretsiz; kayıt bir dakikadan az sürer.',
        ];

        $map = [
            'test1' => [
                'title' => '7 Günlük Test — Ücretsiz Kayıt | Gönül Köprüsü',
                'description' => $cityPrefix.'Düşük bütçeli Ads testi: ücretsiz kayıt, Google ile hızlı üyelik, güvenli tanışma.',
                'headline' => $cityName ? $cityName.'’da ücretsiz başla' : 'Ücretsiz başla — kart bilgisi yok',
                'lead' => 'Tek dokunuşla Google veya e-posta ile kayıt ol. Ciddi ilişki odaklı, moderasyonlu ortam.',
            ],
            'istanbul' => [
                'title' => 'İstanbul Tanışma — Ücretsiz Kayıt | Gönül Köprüsü',
                'description' => 'İstanbul’da ciddi ilişki ve güvenli tanışma. Ücretsiz üye ol, ilçe filtresiyle keşfet.',
                'headline' => 'İstanbul’da ücretsiz tanış',
                'lead' => 'Kadıköy’den Başakşehir’e — İstanbul üyeleriyle güvenli sohbet. Kadınlarda mesaj ücretsiz.',
            ],
            'ankara' => [
                'title' => 'Ankara Tanışma — Ücretsiz Kayıt | Gönül Köprüsü',
                'description' => 'Ankara’da evlilik odaklı tanışma. Ücretsiz kayıt, güvenli sohbet.',
                'headline' => 'Ankara’da ücretsiz üye ol',
                'lead' => 'Başkentte ciddi niyetli üyelerle tanış. Çankaya ve çevresi dahil keşif.',
            ],
            'izmir' => [
                'title' => 'İzmir Tanışma — Ücretsiz Kayıt | Gönül Köprüsü',
                'description' => 'İzmir’de güvenli tanışma ve ciddi ilişki. Ücretsiz üye ol.',
                'headline' => 'İzmir’de ücretsiz başla',
                'lead' => 'Karşıyaka, Bornova, Konak… Ege’de saygılı, moderasyonlu tanışma.',
            ],
            'weekly' => [
                'title' => 'Bu Hafta Ücretsiz Kayıt — Gönül Köprüsü',
                'description' => $cityPrefix.'Haftalık kampanya: ücretsiz kayıt, davet ödülleri, güvenli sohbet.',
                'headline' => 'Bu hafta ücretsiz katıl',
                'lead' => 'Kayıt ol, profilini tamamla, arkadaşını davet ederek ödül kazan.',
            ],
        ];

        return array_merge($defaults, $map[$campaign] ?? []);
    }
}
