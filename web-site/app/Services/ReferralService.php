<?php

namespace App\Services;

use App\Models\PremiumSubscription;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Str;

final class ReferralService
{
    public function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    public function ensureCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        $code = $this->generateUniqueCode();
        $user->forceFill(['referral_code' => $code])->saveQuietly();

        return $code;
    }

    /** Herkese açık davet sayfası (/davet/{kod}) — kayıt + UTM. */
    public function inviteUrl(User $user): string
    {
        $code = $this->ensureCode($user);

        return url('/davet/'.$code.'?utm_source=invite&utm_medium=share&utm_campaign=referral');
    }

    public function registerUrl(User $user): string
    {
        $code = $this->ensureCode($user);

        return url('/register?ref='.$code.'&utm_source=invite&utm_medium=share&utm_campaign=referral');
    }

    public function whatsappShareUrl(User $user, string $shareText): string
    {
        return 'https://wa.me/?text='.rawurlencode(trim($shareText).' '.$this->inviteUrl($user));
    }

    public function findReferrerByCode(?string $code): ?User
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return null;
        }

        return User::where('referral_code', $code)->where('role', 'user')->first();
    }

    public function attachReferral(User $newUser, ?User $referrer): void
    {
        if (! $referrer || $referrer->id === $newUser->id) {
            return;
        }

        if ($newUser->referred_by_user_id) {
            return;
        }

        $newUser->forceFill(['referred_by_user_id' => $referrer->id])->saveQuietly();

        $created = Referral::firstOrCreate(
            ['referred_id' => $newUser->id],
            [
                'referrer_id' => $referrer->id,
                'created_at' => now(),
            ]
        );

        // Ödül yalnızca ilk bağlanmada
        if (! $created->wasRecentlyCreated) {
            return;
        }

        $this->grantReward($referrer);
    }

    /**
     * Erkek: +REFERRAL_REWARD_DAYS deneme + aynı süre premium abonelik.
     * Kadın: 24 saat profil boost (öne çıkarma / rozet etkisi).
     */
    public function grantReward(User $referrer): void
    {
        $days = User::REFERRAL_REWARD_DAYS;

        if ($referrer->gender === 'male') {
            $base = $referrer->trial_ends_at && $referrer->trial_ends_at->isFuture()
                ? $referrer->trial_ends_at
                : now();
            $referrer->forceFill([
                'trial_ends_at' => $base->copy()->addDays($days),
            ])->saveQuietly();

            $this->grantPremiumDays($referrer, $days);
        } else {
            $boostBase = $referrer->boost_until && $referrer->boost_until->isFuture()
                ? $referrer->boost_until
                : now();
            $referrer->forceFill([
                'boost_until' => $boostBase->copy()->addDay(),
            ])->saveQuietly();
        }
    }

    public function grantPremiumDays(User $user, int $days): void
    {
        if ($days < 1 || $user->gender !== 'male') {
            return;
        }

        $active = PremiumSubscription::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->orderByDesc('expires_at')
            ->first();

        $startsAt = now();
        $expiresAt = ($active?->expires_at && $active->expires_at->isFuture()
            ? $active->expires_at
            : now()
        )->copy()->addDays($days);

        PremiumSubscription::create([
            'user_id' => $user->id,
            'package_type' => 'pro',
            'price_tl' => 0,
            'duration_days' => $days,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'payment_reference' => 'referral-reward-'.$user->id.'-'.now()->timestamp,
            'is_active' => true,
        ]);
    }

    public function referralCount(User $user): int
    {
        return Referral::query()->where('referrer_id', $user->id)->count();
    }

    /**
     * @return list<array{count:int,label:string,reached:bool,current:bool}>
     */
    public function milestones(User $user): array
    {
        $count = $this->referralCount($user);
        $defs = [
            ['count' => 1, 'label' => 'İlk davet'],
            ['count' => 3, 'label' => '3 davet'],
            ['count' => 5, 'label' => '5 davet'],
            ['count' => 10, 'label' => '10 davet'],
        ];

        $next = null;
        foreach ($defs as $def) {
            if ($count < $def['count']) {
                $next = $def['count'];
                break;
            }
        }

        return array_map(function (array $def) use ($count, $next) {
            return [
                'count' => $def['count'],
                'label' => $def['label'],
                'reached' => $count >= $def['count'],
                'current' => $next !== null && $def['count'] === $next,
            ];
        }, $defs);
    }

    public function nextMilestone(User $user): ?array
    {
        foreach ($this->milestones($user) as $milestone) {
            if (! empty($milestone['current'])) {
                $left = max(0, $milestone['count'] - $this->referralCount($user));

                return [
                    'count' => $milestone['count'],
                    'label' => $milestone['label'],
                    'left' => $left,
                ];
            }
        }

        return null;
    }

    public function leaderboard(int $limit = 8): array
    {
        return Referral::query()
            ->selectRaw('referrer_id, COUNT(*) as total')
            ->groupBy('referrer_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->with('referrer:id,username,profile_photo_url,city,gender')
            ->get()
            ->map(fn ($row) => [
                'username' => $row->referrer?->username ?? 'üye',
                'city' => $row->referrer?->city,
                'photo' => $row->referrer?->profile_photo_url,
                'total' => (int) $row->total,
            ])
            ->all();
    }
}
