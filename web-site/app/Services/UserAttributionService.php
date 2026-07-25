<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

final class UserAttributionService
{
    public function captureFromRequest(Request $request): void
    {
        $ref = $request->input('ref', $request->query('ref'));
        if (filled($ref)) {
            session(['growth_ref' => strtoupper(trim((string) $ref))]);
        }

        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $key) {
            $value = $request->input($key, $request->query($key));
            if (filled($value)) {
                session(['growth_'.$key => substr(trim((string) $value), 0, 120)]);
            }
        }

        // SEO şehir sayfalarından kayıt → keşif yönlendirmesi
        $city = trim((string) $request->input('city', $request->query('city', '')));
        if ($city !== '') {
            session(['growth_city' => substr($city, 0, 100)]);
        }
        $district = trim((string) $request->input('district', $request->query('district', '')));
        if ($district !== '') {
            session(['growth_district' => substr($district, 0, 100)]);
        }
        $country = trim((string) $request->input('country', $request->query('country', '')));
        if ($country !== '') {
            session(['growth_country' => substr($country, 0, 100)]);
        }
    }

    /** Kayıt sonrası: şehir SEO dönüşümü varsa filtreli keşif, yoksa akış. */
    public function postSignupRedirectUrl(): string
    {
        $city = trim((string) session('growth_city', ''));
        if ($city === '') {
            return route('feed');
        }

        $params = array_filter([
            'country' => session('growth_country') ?: 'Türkiye',
            'city' => $city,
            'district' => session('growth_district') ?: null,
        ], fn ($v) => $v !== null && $v !== '');

        session()->forget(['growth_city', 'growth_district', 'growth_country']);

        return route('users.index', $params);
    }

    /** @return array<string, string|null> */
    public function sessionPayload(): array
    {
        return [
            'ref' => session('growth_ref'),
            'utm_source' => session('growth_utm_source'),
            'utm_medium' => session('growth_utm_medium'),
            'utm_campaign' => session('growth_utm_campaign'),
        ];
    }

    public function applyToNewUser(User $user, string $registrationSource = 'email'): void
    {
        $payload = $this->sessionPayload();

        $user->forceFill([
            'utm_source' => $payload['utm_source'],
            'utm_medium' => $payload['utm_medium'],
            'utm_campaign' => $payload['utm_campaign'],
            'registration_source' => $registrationSource,
        ])->saveQuietly();

        if ($payload['ref']) {
            app(ReferralService::class)->attachReferral(
                $user,
                app(ReferralService::class)->findReferrerByCode($payload['ref'])
            );
        }

        app(ReferralService::class)->ensureCode($user);
    }
}
