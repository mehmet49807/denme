<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LocationDataService;
use App\Services\PublishedBlogFaqService;
use App\Support\FeaturedCities;
use App\Support\SeoHelper;
use Illuminate\View\View;

class CitySeoPageController extends Controller
{
    public function __construct(
        private LocationDataService $locations,
        private PublishedBlogFaqService $blogFaq,
    ) {}

    public function show(string $slug): View
    {
        $city = $this->locations->resolveCitySlug($slug);
        if (! $city) {
            abort(404);
        }

        $country = 'Türkiye';
        SeoHelper::setLocationPage($city, null, $country);
        SeoHelper::set('canonical', route('city.seo', $slug));

        $memberCount = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('country', $country)
            ->where('city', $city)
            ->count();

        $femaleCount = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('country', $country)
            ->where('city', $city)
            ->where('gender', 'female')
            ->count();

        $maleCount = max(0, $memberCount - $femaleCount);

        $cityLinks = FeaturedCities::links($this->locations);
        $relatedPosts = collect($this->blogFaq->blogPosts())
            ->filter(function (array $post) use ($city, $slug) {
                $hay = mb_strtolower(($post['title'] ?? '').' '.($post['description'] ?? '').' '.implode(' ', $post['keywords'] ?? []), 'UTF-8');

                return str_contains($hay, mb_strtolower($city, 'UTF-8'))
                    || str_contains($hay, $slug)
                    || str_contains($hay, 'şehir')
                    || str_contains($hay, 'tanışma');
            })
            ->take(3)
            ->values()
            ->all();

        if ($relatedPosts === []) {
            $relatedPosts = collect($this->blogFaq->blogPosts())->take(3)->values()->all();
        }

        $faqItems = collect($this->blogFaq->faqItems())->take(4)->values()->all();

        return view('web.city-seo', [
            'slug' => $slug,
            'city' => $city,
            'country' => $country,
            'memberCount' => $memberCount,
            'femaleCount' => $femaleCount,
            'maleCount' => $maleCount,
            'cityLinks' => $cityLinks,
            'relatedPosts' => $relatedPosts,
            'faqItems' => $faqItems,
            'registerUrl' => route('register', [
                'utm_source' => 'seo',
                'utm_medium' => 'city',
                'utm_campaign' => $slug,
            ]),
            'instagramUrl' => 'https://www.instagram.com/gonulkoprusucom/?utm_source=seo&utm_medium=city&utm_campaign='.$slug,
        ]);
    }
}
