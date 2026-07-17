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
