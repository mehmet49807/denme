<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GenderFilterService;
use App\Services\LocationDataService;
use App\Support\LocationUrl;
use App\Support\SeoHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationUsersPageController extends Controller
{
    public function __construct(
        private GenderFilterService $genderFilter,
        private LocationDataService $locations,
        private LocationUrl $locationUrl,
    ) {}

    public function search(Request $request): View
    {
        return view('web.location-users', [
            'country' => old('country', $request->query('country', '')),
            'city' => old('city', $request->query('city', '')),
            'district' => old('district', $request->query('district', '')),
            'users' => null,
            'locationLabel' => 'Konum Ara',
            'showResults' => false,
        ]);
    }

    public function find(Request $request): RedirectResponse|View
    {
        $country = trim((string) $request->query('country', ''));
        $city = trim((string) $request->query('city', ''));
        $district = trim((string) $request->query('district', ''));

        if ($country === '' || $city === '') {
            return redirect()
                ->route('locations.search', array_filter([
                    'country' => $country,
                    'city' => $city,
                    'district' => $district,
                ]))
                ->withErrors(['location' => 'Lütfen ülke ve şehir seçin.']);
        }

        if (! $this->locations->isValid($country, $city, $district !== '' ? $district : null)) {
            abort(404);
        }

        return redirect()->route(
            'locations.users',
            $this->locationUrl->routeParams($country, $city, $district !== '' ? $district : null)
        );
    }

    public function index(Request $request, string $countrySlug, string $citySlug, ?string $districtSlug = null): View|RedirectResponse
    {
        [$country, $city, $district] = $this->locationUrl->resolveSegments(
            $countrySlug,
            $citySlug,
            $districtSlug
        );

        if (! $country || ! $city) {
            abort(404);
        }

        if (! $this->locationUrl->isCanonical($countrySlug, $citySlug, $districtSlug, $country, $city, $district)) {
            return redirect()->route(
                'locations.users',
                $this->locationUrl->routeParams($country, $city, $district !== '' ? $district : null),
                301
            );
        }

        SeoHelper::setLocationPage($city, $district !== '' ? $district : null, $country);

        $viewer = $request->user();

        $users = User::where('role', 'user')
            ->where('is_banned', false)
            ->where('country', $country)
            ->where('city', $city)
            ->when($district !== '', fn ($q) => $q->where('district', $district))
            ->where('id', '!=', $viewer->id)
            ->where(function ($q) use ($viewer) {
                $this->genderFilter->applyDiscoveryFilters($q, $viewer);
            })
            ->withCount(['posts' => fn ($q) => $q->where('is_active', true)])
            ->latest('last_active_at')
            ->paginate(24)
            ->withQueryString();

        $locationLabel = $this->locations->formatLabel($country, $city, $district ?: null);

        return view('web.location-users', [
            'country' => $country,
            'city' => $city,
            'district' => $district,
            'users' => $users,
            'locationLabel' => $locationLabel,
            'showResults' => true,
        ]);
    }
}
