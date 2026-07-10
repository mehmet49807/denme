<?php

namespace App\Support;

use App\Services\LocationDataService;

class LocationUrl
{
    public function __construct(private LocationDataService $locations) {}

    public function slug(string $value): string
    {
        return $this->locations->slug($value);
    }

    /**
     * @return array{country: string, city: string, district?: string}
     */
    public function routeParams(string $country, string $city, ?string $district = null): array
    {
        $params = [
            'country' => $this->slug($country),
            'city' => $this->slug($city),
        ];

        if ($district !== null && $district !== '') {
            $params['district'] = $this->slug($district);
        }

        return $params;
    }

    public function url(string $country, string $city, ?string $district = null): string
    {
        return route('locations.users', $this->routeParams($country, $city, $district));
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: string}
     */
    public function resolveSegments(string $countrySlug, string $citySlug, ?string $districtSlug = null): array
    {
        $country = $this->locations->resolveCountrySlug($countrySlug);
        if (! $country) {
            return [null, null, ''];
        }

        $city = $this->locations->resolveCitySlug($citySlug, $country);
        if (! $city) {
            return [null, null, ''];
        }

        $district = '';
        if ($districtSlug !== null && $districtSlug !== '') {
            $resolvedDistrict = $this->locations->resolveDistrictSlug($country, $city, $districtSlug);
            if (! $resolvedDistrict) {
                return [null, null, ''];
            }
            $district = $resolvedDistrict;
        }

        return [$country, $city, $district];
    }

    public function isCanonical(string $countrySlug, string $citySlug, ?string $districtSlug, string $country, string $city, string $district): bool
    {
        $canonical = $this->routeParams($country, $city, $district !== '' ? $district : null);

        return $countrySlug === $canonical['country']
            && $citySlug === $canonical['city']
            && (($districtSlug ?? '') === ($canonical['district'] ?? ''));
    }
}
