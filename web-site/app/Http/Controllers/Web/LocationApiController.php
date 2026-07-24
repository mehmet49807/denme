<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\LocationDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    public function __construct(
        private LocationDataService $locations,
    ) {}

    public function countries(): JsonResponse
    {
        $names = $this->locations->countries();
        $meta = [];
        $countryMeta = class_exists(\App\Services\CountryMetaService::class)
            ? app(\App\Services\CountryMetaService::class)
            : (class_exists(\App\Support\CountryMetaService::class) ? app(\App\Support\CountryMetaService::class) : null);

        foreach ($names as $name) {
            $iso = '';
            $flag = '';
            if ($countryMeta && method_exists($countryMeta, 'isoForCountry')) {
                $iso = (string) $countryMeta->isoForCountry($name);
            }
            if ($countryMeta && $iso !== '' && method_exists($countryMeta, 'flagUrl')) {
                $flag = (string) $countryMeta->flagUrl($iso);
            }
            $meta[] = [
                'name' => $name,
                'iso' => $iso,
                'flag' => $flag,
            ];
        }

        return response()->json([
            'data' => [
                'countries' => $names,
                'countries_meta' => $meta,
            ],
        ]);
    }

    public function cities(Request $request): JsonResponse
    {
        $country = trim((string) $request->query('country', ''));

        return response()->json([
            'data' => [
                'cities' => $country !== '' ? $this->locations->cities($country) : [],
            ],
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $country = trim((string) $request->query('country', ''));
        $city = trim((string) $request->query('city', ''));

        return response()->json([
            'data' => [
                'districts' => ($country !== '' && $city !== '')
                    ? $this->locations->districts($country, $city)
                    : [],
            ],
        ]);
    }
}
