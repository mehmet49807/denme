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

        SeoHelper::set('title', 'Ücretsiz Kayıt Ol — Gönül Köprüsü Tanışma');
        SeoHelper::set(
            'description',
            ($cityName ? $cityName.' tanışma: ' : '')
            .'Gönül Köprüsü\'ne ücretsiz üye ol. Güvenli sohbet, ciddi ilişki ve evlilik odaklı platform.'
        );
        SeoHelper::set('robots', 'noindex,follow');

        $registerParams = [
            'utm_source' => $source !== '' ? $source : 'ads',
            'utm_medium' => $medium !== '' ? $medium : 'paid',
            'utm_campaign' => $campaign !== '' ? $campaign : 'growth',
        ];

        return view('web.campaign-landing', [
            'cityName' => $cityName,
            'citySlug' => $citySlug,
            'registerUrl' => route('register', $registerParams),
            'googleUrl' => url('auth/google'),
            'instagramUrl' => InstagramUrl::withUtm($source ?: 'ads', $medium ?: 'paid', $campaign ?: 'growth'),
            'cityLinks' => FeaturedCities::links($locations),
            'source' => $source,
            'medium' => $medium,
            'campaign' => $campaign,
        ]);
    }
}
