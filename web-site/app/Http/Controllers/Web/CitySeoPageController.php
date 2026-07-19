<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LocationDataService;
use App\Services\PublishedBlogFaqService;
use App\Services\UserAttributionService;
use App\Support\CitySeoCopy;
use App\Support\FeaturedCities;
use App\Support\InstagramUrl;
use App\Support\SeoDistricts;
use App\Support\SeoHelper;
use App\Support\SeoSchema;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CitySeoPageController extends Controller
{
    public function __construct(
        private LocationDataService $locations,
        private PublishedBlogFaqService $blogFaq,
    ) {}

    public function show(Request $request, string $slug): View
    {
        return $this->render($request, $slug, null);
    }

    public function showDistrict(Request $request, string $slug, string $districtSlug): View
    {
        return $this->render($request, $slug, $districtSlug);
    }

    private function render(Request $request, string $slug, ?string $districtSlug): View
    {
        app(UserAttributionService::class)->captureFromRequest($request);

        $city = $this->locations->resolveCitySlug($slug);
        if (! $city) {
            abort(404);
        }

        $country = 'Türkiye';
        $district = null;
        if ($districtSlug !== null && $districtSlug !== '') {
            $allowed = SeoDistricts::forCitySlug($slug);
            if ($allowed === []) {
                abort(404);
            }
            foreach ($allowed as $name) {
                if (SeoDistricts::slug($name) === $districtSlug) {
                    $district = $name;
                    break;
                }
            }
            if (! $district) {
                abort(404);
            }
        }

        SeoHelper::setLocationPage($city, $district, $country);
        $canonical = $district
            ? route('city.seo.district', ['slug' => $slug, 'district' => SeoDistricts::slug($district)])
            : route('city.seo', $slug);
        SeoHelper::set('canonical', $canonical);

        $memberQuery = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('country', $country)
            ->where('city', $city);
        if ($district) {
            $memberQuery->where('district', $district);
        }
        $memberCount = (clone $memberQuery)->count();
        $femaleCount = (clone $memberQuery)->where('gender', 'female')->count();
        $maleCount = max(0, $memberCount - $femaleCount);

        $cityLinks = FeaturedCities::links($this->locations);
        $relatedPosts = collect($this->blogFaq->blogPosts())
            ->filter(function (array $post) use ($city, $slug, $district) {
                $hay = mb_strtolower(
                    ($post['title'] ?? '').' '.($post['description'] ?? '').' '.implode(' ', $post['keywords'] ?? []),
                    'UTF-8'
                );
                $needles = [mb_strtolower($city, 'UTF-8'), $slug, 'şehir', 'tanışma'];
                if ($district) {
                    $needles[] = mb_strtolower($district, 'UTF-8');
                }
                foreach ($needles as $needle) {
                    if ($needle !== '' && str_contains($hay, $needle)) {
                        return true;
                    }
                }

                return false;
            })
            ->take(3)
            ->values()
            ->all();

        if ($relatedPosts === []) {
            $relatedPosts = collect($this->blogFaq->blogPosts())->take(3)->values()->all();
        }

        $copy = $district
            ? CitySeoCopy::forDistrict($city, $slug, $district, $memberCount, $femaleCount, $maleCount)
            : CitySeoCopy::forCity($city, $slug, $memberCount, $femaleCount, $maleCount);
        $globalFaq = collect($this->blogFaq->faqItems())->take(2)->values()->all();
        $faqItems = array_merge($copy['faqs'], $globalFaq);

        $placeLabel = $district ? ($district.', '.$city) : $city;
        $breadcrumb = $district
            ? SeoSchema::breadcrumb(
                $district.' tanışma',
                $canonical,
                $city.' tanışma',
                route('city.seo', $slug)
            )
            : SeoSchema::breadcrumb($city.' tanışma', $canonical);
        $jsonLd = SeoSchema::faqPage($faqItems, $breadcrumb);
        $jsonLd['@graph'][] = [
            '@type' => 'WebPage',
            'name' => $placeLabel.' Tanışma, Sohbet ve Evlilik Sitesi',
            'url' => $canonical,
            'description' => SeoHelper::get('description'),
            'about' => [
                '@type' => 'Place',
                'name' => $placeLabel.', Türkiye',
            ],
        ];

        $districtLinks = [];
        foreach (SeoDistricts::forCitySlug($slug) as $name) {
            $districtLinks[] = [
                'name' => $name,
                'slug' => SeoDistricts::slug($name),
            ];
        }

        return view('web.city-seo', [
            'slug' => $slug,
            'city' => $city,
            'district' => $district,
            'country' => $country,
            'memberCount' => $memberCount,
            'femaleCount' => $femaleCount,
            'maleCount' => $maleCount,
            'cityLinks' => $cityLinks,
            'districtLinks' => $districtLinks,
            'relatedPosts' => $relatedPosts,
            'faqItems' => $faqItems,
            'seoLead' => $copy['lead'],
            'seoWhy' => $copy['why'],
            'jsonLd' => $jsonLd,
            'registerUrl' => route('register', [
                'utm_source' => 'seo',
                'utm_medium' => $district ? 'district' : 'city',
                'utm_campaign' => $district ? ($slug.'-'.SeoDistricts::slug($district)) : $slug,
            ]),
            'campaignUrl' => route('campaign.landing', [
                'utm_source' => 'seo',
                'utm_medium' => $district ? 'district' : 'city',
                'utm_campaign' => $slug,
                'city' => $slug,
            ]),
            'instagramUrl' => InstagramUrl::withUtm('seo', $district ? 'district' : 'city', $slug),
        ]);
    }
}
