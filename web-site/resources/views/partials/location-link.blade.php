@php
    $routeParams = app(\App\Support\LocationUrl::class)->routeParams(
        $country ?? 'Türkiye',
        $city,
        ! empty($district) ? $district : null
    );
    $label = app(\App\Services\LocationDataService::class)->formatLabel($country ?? 'Türkiye', $city, $district ?? null);
@endphp
<a href="{{ route('locations.users', $routeParams) }}" class="location-link {{ $class ?? '' }}">
    {{ $label }}
</a>
