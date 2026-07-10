<?php

namespace App\Services;

class LocationDataService
{
    private ?array $locations = null;

    public function countries(): array
    {
        return array_keys($this->all());
    }

    public function cities(string $country): array
    {
        $data = $this->country($country);
        if (! $data) {
            return [];
        }

        $cities = $data['cities'];

        return array_is_list($cities) ? $cities : array_keys($cities);
    }

    public function districts(string $country, string $city): array
    {
        $data = $this->country($country);
        if (! $data || empty($data['has_districts']) || array_is_list($data['cities'])) {
            return [];
        }

        return $data['cities'][$city] ?? [];
    }

    public function hasDistricts(string $country): bool
    {
        $data = $this->country($country);

        return (bool) ($data['has_districts'] ?? false);
    }

    public function requiresDistrict(string $country, string $city): bool
    {
        return $this->districts($country, $city) !== [];
    }

    public function isValid(string $country, string $city, ?string $district = null): bool
    {
        if (! $this->isValidCity($country, $city)) {
            return false;
        }

        if ($this->requiresDistrict($country, $city)) {
            return in_array($district, $this->districts($country, $city), true);
        }

        return $district === null || $district === '';
    }

    public function isValidCity(string $country, string $city): bool
    {
        return in_array($city, $this->cities($country), true);
    }

    public function normalizeDistrict(string $country, string $city, ?string $district): string
    {
        if ($this->requiresDistrict($country, $city)) {
            return (string) $district;
        }

        return '';
    }

    public function formatLabel(?string $country, string $city, ?string $district = null): string
    {
        $parts = array_filter([
            $country,
            $city,
            $district ?: null,
        ], fn ($part) => $part !== null && $part !== '');

        return implode(' — ', $parts);
    }

    public function slug(string $value): string
    {
        $value = trim($value);
        $map = [
            'İ' => 'I', 'I' => 'I', 'ı' => 'i',
            'Ğ' => 'G', 'ğ' => 'g',
            'Ü' => 'U', 'ü' => 'u',
            'Ş' => 'S', 'ş' => 's',
            'Ö' => 'O', 'ö' => 'o',
            'Ç' => 'C', 'ç' => 'c',
        ];
        $slug = strtr($value, $map);
        $slug = mb_strtolower($slug, 'UTF-8');

        return trim(preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '', '-');
    }

    public function citySlug(string $city): string
    {
        return $this->slug($city);
    }

    public function resolveCountrySlug(string $slug): ?string
    {
        $slug = trim(urldecode($slug));
        if ($slug === '') {
            return null;
        }

        foreach ($this->countries() as $country) {
            if ($this->slug($country) === $slug || $country === $slug) {
                return $country;
            }
        }

        return null;
    }

    public function resolveCitySlug(string $slug, string $country = 'Türkiye'): ?string
    {
        $slug = trim(urldecode($slug));
        if ($slug === '') {
            return null;
        }

        foreach ($this->cities($country) as $city) {
            if ($this->slug($city) === $slug || $city === $slug) {
                return $city;
            }
        }

        return null;
    }

    public function resolveDistrictSlug(string $country, string $city, string $slug): ?string
    {
        $slug = trim(urldecode($slug));
        if ($slug === '') {
            return null;
        }

        foreach ($this->districts($country, $city) as $district) {
            if ($this->slug($district) === $slug || $district === $slug) {
                return $district;
            }
        }

        return null;
    }

    /** @return list<string> */
    public function seoCitySlugs(string $country = 'Türkiye'): array
    {
        return array_map(fn (string $city) => $this->slug($city), $this->cities($country));
    }

    private function country(string $country): ?array
    {
        return $this->all()[$country] ?? null;
    }

    private function all(): array
    {
        if ($this->locations === null) {
            $this->locations = require database_path('data/world-locations.php');
        }

        return $this->locations;
    }
}
