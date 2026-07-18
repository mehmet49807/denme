<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Yeni üye e-posta dizisi: fotoğraf, davet, trial→premium, yeniden etkileşim.
 * Setup cron (/setup/cron) üzerinden çağrılır.
 */
final class GrowthLifecycleService
{
    public function __construct(private UserMailService $mail) {}

    /** @return array<string, int> */
    public function run(int $limit = 40): array
    {
        $stats = [
            'profile_nudge' => 0,
            'invite_campaign' => 0,
            'trial_premium' => 0,
            're_engagement' => 0,
            'skipped' => 0,
        ];

        if (! Schema::hasTable('users')) {
            return $stats;
        }

        $per = max(5, (int) ceil($limit / 4));

        $stats['profile_nudge'] = $this->sendProfileCompleteNudges($per);
        $stats['invite_campaign'] = $this->sendInviteCampaigns($per);
        $stats['trial_premium'] = $this->sendTrialPremiumNudges($per);
        $stats['re_engagement'] = $this->sendReEngagement($per);

        return $stats;
    }

    private function sendProfileCompleteNudges(int $limit): int
    {
        $sent = 0;
        $users = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->whereNull('profile_photo_url')
            ->where('created_at', '<=', now()->subDay())
            ->where('created_at', '>=', now()->subDays(7))
            ->where(function ($q) {
                $q->whereNull('last_lifecycle_email_at')
                    ->orWhere('last_lifecycle_email_at', '<', now()->subDays(2));
            })
            ->orderBy('id')
            ->limit($limit * 2)
            ->get();

        foreach ($users as $user) {
            if ($sent >= $limit) {
                break;
            }
            if ($this->alreadySent($user, 'profile_complete', 5)) {
                continue;
            }
            if ($this->mail->sendLifecycle($user, 'profile_complete')) {
                $this->markSent($user);
                $sent++;
            }
        }

        return $sent;
    }

    private function sendInviteCampaigns(int $limit): int
    {
        $sent = 0;
        $users = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('created_at', '<=', now()->subDays(2))
            ->where('created_at', '>=', now()->subDays(45))
            ->where(function ($q) {
                $q->whereNull('last_lifecycle_email_at')
                    ->orWhere('last_lifecycle_email_at', '<', now()->subDays(4));
            })
            ->orderByDesc('last_active_at')
            ->limit($limit * 3)
            ->get();

        foreach ($users as $user) {
            if ($sent >= $limit) {
                break;
            }

            try {
                $hasInvite = Referral::query()->where('referrer_id', $user->id)->exists();
            } catch (\Throwable) {
                $hasInvite = false;
            }

            if ($hasInvite) {
                continue;
            }

            $template = isset($this->mail->templates()['invite_friends'])
                ? 'invite_friends'
                : 'premium_invite';

            if ($this->alreadySent($user, $template, 12)) {
                continue;
            }

            if ($this->mail->sendLifecycle($user, $template)) {
                $this->markSent($user);
                $sent++;
            }
        }

        return $sent;
    }

    /** Erkek: trial bitimine 24s kala veya bitişten sonra 3 gün içinde premium daveti */
    private function sendTrialPremiumNudges(int $limit): int
    {
        if (! Schema::hasColumn('users', 'trial_ends_at')) {
            return 0;
        }

        $sent = 0;
        $users = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('gender', 'male')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now()->addDay())
            ->where('trial_ends_at', '>=', now()->subDays(3))
            ->where(function ($q) {
                $q->whereNull('last_lifecycle_email_at')
                    ->orWhere('last_lifecycle_email_at', '<', now()->subDays(2));
            })
            ->orderBy('trial_ends_at')
            ->limit($limit * 2)
            ->get();

        foreach ($users as $user) {
            if ($sent >= $limit) {
                break;
            }
            if ($user->isPremium()) {
                continue;
            }
            if ($this->alreadySent($user, 'premium_invite', 10)) {
                continue;
            }
            if ($this->mail->sendLifecycle($user, 'premium_invite')) {
                $this->markSent($user);
                $sent++;
            }
        }

        return $sent;
    }

    /** 3 / 7 / 14 gün sessiz üye → re_engagement */
    private function sendReEngagement(int $limit): int
    {
        $sent = 0;
        $users = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('created_at', '<=', now()->subDays(3))
            ->where(function ($q) {
                $q->whereNull('last_active_at')
                    ->orWhere('last_active_at', '<', now()->subDays(3));
            })
            ->where(function ($q) {
                $q->whereNull('last_lifecycle_email_at')
                    ->orWhere('last_lifecycle_email_at', '<', now()->subDays(6));
            })
            ->orderBy('last_active_at')
            ->limit($limit * 3)
            ->get();

        foreach ($users as $user) {
            if ($sent >= $limit) {
                break;
            }

            $inactiveDays = $user->last_active_at
                ? $user->last_active_at->diffInDays(now())
                : $user->created_at?->diffInDays(now()) ?? 0;

            if ($inactiveDays < 3) {
                continue;
            }

            // 3 / 7 / 14 gün pencerelerinde bir kez (yaklaşık)
            $inWindow = ($inactiveDays >= 3 && $inactiveDays <= 4)
                || ($inactiveDays >= 7 && $inactiveDays <= 9)
                || ($inactiveDays >= 14 && $inactiveDays <= 16);

            if (! $inWindow && $inactiveDays < 14) {
                continue;
            }

            if ($this->alreadySent($user, 're_engagement', 13)) {
                continue;
            }

            if ($this->mail->sendLifecycle($user, 're_engagement')) {
                $this->markSent($user);
                $sent++;
            }
        }

        return $sent;
    }

    private function markSent(User $user): void
    {
        $user->forceFill(['last_lifecycle_email_at' => now()])->saveQuietly();
    }

    private function alreadySent(User $user, string $templateKey, int $withinDays): bool
    {
        try {
            if (! Schema::hasTable('email_logs')) {
                return false;
            }

            return DB::table('email_logs')
                ->where('user_id', $user->id)
                ->where('template_key', $templateKey)
                ->where('status', 'sent')
                ->where('created_at', '>=', now()->subDays($withinDays))
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}
