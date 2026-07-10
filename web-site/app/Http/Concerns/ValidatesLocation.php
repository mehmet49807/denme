<?php

namespace App\Http\Concerns;

use App\Services\LocationDataService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesLocation
{
    protected function validateLocationInput(
        Request $request,
        LocationDataService $locations,
        bool $required = true,
        bool $requireDistrict = true,
    ): array {
        if (! $required && ! $request->hasAny(['country', 'city', 'district'])) {
            return [];
        }

        $presence = 'required';

        $validated = $request->validate([
            'country' => "{$presence}|string|max:100",
            'city' => "{$presence}|string|max:100",
            'district' => 'nullable|string|max:100',
        ]);

        if (! isset($validated['country'], $validated['city'])) {
            return $validated;
        }

        $district = $validated['district'] ?? null;

        if ($requireDistrict) {
            if ($locations->requiresDistrict($validated['country'], $validated['city']) && ! $district) {
                throw ValidationException::withMessages([
                    'district' => 'İlçe seçimi zorunludur.',
                ]);
            }

            if (! $locations->isValid($validated['country'], $validated['city'], $district)) {
                throw ValidationException::withMessages([
                    'city' => 'Geçersiz ülke, şehir veya ilçe seçimi.',
                ]);
            }

            $validated['district'] = $locations->normalizeDistrict(
                $validated['country'],
                $validated['city'],
                $district
            );
        } else {
            if (! $locations->isValidCity($validated['country'], $validated['city'])) {
                throw ValidationException::withMessages([
                    'city' => 'Geçersiz ülke veya şehir seçimi.',
                ]);
            }

            $validated['district'] = '';
        }

        return $validated;
    }
}
