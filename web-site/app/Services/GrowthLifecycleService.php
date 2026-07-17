<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Yeni üye e-posta dizisi + mevcut üyelere davet kampanyası.
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
            'skipped' => 0,
        ];

        if (! Schema::hasTable('users')) {
            return $stats;
        }

        $stats['profile_nudge'] = $this->sendProfileCompleteNudges($limit);
        $stats['invite_campaign'] = $this->sendInviteCampaigns($limit);

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
            ->where('created_at', '>=', now()->subDays(5))
            ->where(function ($q) {
                $q->whereNull('last_lifecycle_email_at')
                    ->orWhere('last_lifecycle_email_at', '<', now()->subDays(2));
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($users as $user) {
            if ($this->mail->sendLifecycle($user, 'profile_complete')) {
                $user->forceFill(['last_lifecycle_email_at' => now()])->saveQuietly();
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
            ->where('created_at', '>=', now()->subDays(30))
            ->where(function ($q) {
                $q->whereNull('last_lifecycle_email_at')
                    ->orWhere('last_lifecycle_email_at', '<', now()->subDays(5));
            })
            ->orderByDesc('last_active_at')
            ->limit($limit * 2)
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

            if ($this->mail->sendLifecycle($user, $template)) {
                $user->forceFill(['last_lifecycle_email_at' => now()])->saveQuietly();
                $sent++;
            }
        }

        return $sent;
    }
}
