<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LocationDataService;
use Illuminate\View\View;

class CitySeoPageController extends Controller
{
    public function __construct(private LocationDataService $locations) {}

    public function show(string $slug): View
    {
        $city = $this->locations->resolveCitySlug($slug);
        if (! $city) {
            abort(404);
        }

        $country = 'Türkiye';
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

        return view('web.city-seo', [
            'slug' => $slug,
            'city' => $city,
            'country' => $country,
            'memberCount' => $memberCount,
            'femaleCount' => $femaleCount,
            'maleCount' => $maleCount,
        ]);
    }
}
