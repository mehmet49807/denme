<?php

namespace App\Services;

use App\Models\PremiumSubscription;
use App\Models\Referral;
use App\Models\User;
use Carbon\Carbon;
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
        return $this->weeklyLeaderboard($limit);
    }

    /** Bu haftanın Pazartesi 00:00 — Pazar 23:59 (Europe/Istanbul). */
    public function currentWeekBounds(): array
    {
        $tz = 'Europe/Istanbul';
        $start = now($tz)->startOfWeek(Carbon::MONDAY)->utc();
        $end = now($tz)->endOfWeek(Carbon::SUNDAY)->utc();

        return [$start, $end];
    }

    public function weekKey(?\DateTimeInterface $at = null): string
    {
        $at = Carbon::parse($at ?? now(), 'Europe/Istanbul');

        return $at->isoFormat('GGGG-[W]WW');
    }

    public function weeklyLeaderboard(int $limit = 8): array
    {
        [$start, $end] = $this->currentWeekBounds();

        return Referral::query()
            ->selectRaw('referrer_id, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('referrer_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->with('referrer:id,username,profile_photo_url,city,gender')
            ->get()
            ->map(fn ($row) => [
                'user_id' => (int) $row->referrer_id,
                'username' => $row->referrer?->username ?? 'üye',
                'city' => $row->referrer?->city,
                'photo' => $row->referrer?->profile_photo_url,
                'gender' => $row->referrer?->gender,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * Geçen haftanın 1.'sine ödül ver (bir kez). Erkek +7 gün Pro, kadın +48s boost.
     */
    public function ensurePreviousWeekWinnerRewarded(): ?array
    {
        $tz = 'Europe/Istanbul';
        $prevStart = now($tz)->subWeek()->startOfWeek(Carbon::MONDAY)->utc();
        $prevEnd = now($tz)->subWeek()->endOfWeek(Carbon::SUNDAY)->utc();
        $weekKey = $this->weekKey(now($tz)->subWeek());
        $cacheKey = 'referral_week_rewarded:'.$weekKey;

        if (cache()->get($cacheKey)) {
            return null;
        }

        $winner = Referral::query()
            ->selectRaw('referrer_id, COUNT(*) as total')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->groupBy('referrer_id')
            ->orderByDesc('total')
            ->havingRaw('COUNT(*) > 0')
            ->first();

        if (! $winner) {
            cache()->forever($cacheKey, ['empty' => true]);

            return null;
        }

        $user = User::query()->find($winner->referrer_id);
        if (! $user) {
            cache()->forever($cacheKey, ['missing' => true]);

            return null;
        }

        if ($user->gender === 'male') {
            $this->grantPremiumDays($user, 7);
            $base = $user->trial_ends_at && $user->trial_ends_at->isFuture()
                ? $user->trial_ends_at
                : now();
            $user->forceFill(['trial_ends_at' => $base->copy()->addDays(7)])->saveQuietly();
        } else {
            $boostBase = $user->boost_until && $user->boost_until->isFuture()
                ? $user->boost_until
                : now();
            $user->forceFill(['boost_until' => $boostBase->copy()->addHours(48)])->saveQuietly();
        }

        $payload = [
            'week' => $weekKey,
            'user_id' => $user->id,
            'username' => $user->username,
            'total' => (int) $winner->total,
        ];
        cache()->forever($cacheKey, $payload);

        return $payload;
    }
}
