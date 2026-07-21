<?php

namespace App\Services;

use App\Models\Like;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class GrowthOnboardingService
{
    /** İlk 24 saat aktivasyon penceresi */
    public const WINDOW_HOURS = 24;

    public function __construct(
        private ProfileCompletenessService $completeness,
    ) {}

    public function isWithinWindow(User $user): bool
    {
        if (! $user->created_at) {
            return false;
        }

        return $user->created_at->gt(now()->subHours(self::WINDOW_HOURS));
    }

    public function shouldShow(User $user): bool
    {
        if (! $this->isWithinWindow($user)) {
            return false;
        }

        // Davet adımı bonus — checklist'i kilitlemez
        $checklist = collect($this->checklist($user))
            ->reject(fn (array $item) => ($item['key'] ?? '') === 'invite');

        return $checklist->contains(fn (array $item) => ! $item['done']);
    }

    /**
     * @return list<array{key: string, label: string, done: bool, href: string}>
     */
    public function checklist(User $user): array
    {
        $hasPhoto = filled($user->profile_photo_url);
        $likeCount = 0;
        $messageCount = 0;
        $profile = $this->completeness->forUser($user);

        try {
            $likeCount = Like::query()->where('user_id', $user->id)->count();
        } catch (\Throwable) {
        }

        try {
            if (Schema::hasTable('messages') && Schema::hasColumn('messages', 'sender_id')) {
                $messageCount = (int) DB::table('messages')->where('sender_id', $user->id)->count();
            }
        } catch (\Throwable) {
            $messageCount = 0;
        }

        $items = [
            [
                'key' => 'photo',
                'label' => 'Profil fotoğrafı ekle',
                'done' => $hasPhoto,
                'href' => route('profile'),
            ],
            [
                'key' => 'profile_score',
                'label' => 'Profilini %70 tamamla (şu an %'.$profile['percent'].')',
                'done' => $profile['percent'] >= 70,
                'href' => route('profile'),
            ],
            [
                'key' => 'like',
                'label' => '3 gönderiyi beğen',
                'done' => $likeCount >= 3,
                'href' => route('feed'),
            ],
            [
                'key' => 'message',
                'label' => '1 kişiye ilk mesajını gönder',
                'done' => $messageCount >= 1,
                'href' => route('users.index'),
            ],
            [
                'key' => 'invite',
                'label' => 'Arkadaşını davet et',
                'done' => false,
                'href' => route('referral'),
            ],
        ];

        if ($user->gender === 'female') {
            array_unshift($items, [
                'key' => 'women_perk',
                'label' => 'Kimler baktı ve mesajlaşma sende ücretsiz',
                'done' => true,
                'href' => route('profile'),
            ]);
        }

        if ($user->gender === 'male') {
            $trialLabel = $user->isOnTrial()
                ? 'Deneme: '.$user->trialDaysRemaining().' gün / '.$user->trialHoursRemaining().' saat kaldı'
                : ($user->isPremium()
                    ? 'Premium aktif — mesaj ve hikâye açık'
                    : 'Deneme bitti — mesaj için premium gerekli');

            array_unshift($items, [
                'key' => 'trial',
                'label' => $trialLabel,
                'done' => $user->isOnTrial() || $user->isPremium(),
                'href' => route('premium'),
            ]);
        }

        try {
            $inviteDone = \App\Models\Referral::query()->where('referrer_id', $user->id)->exists()
                || (bool) session('growth_invite_shared')
                || (bool) request()->cookie('gk_invite_shared');
            foreach ($items as &$item) {
                if ($item['key'] === 'invite') {
                    $item['done'] = $inviteDone;
                }
            }
            unset($item);
        } catch (\Throwable) {
        }

        return $items;
    }

    public function progress(User $user): array
    {
        $items = $this->checklist($user);
        $done = collect($items)->where('done', true)->count();
        $total = count($items);
        $profile = $this->completeness->forUser($user);

        return [
            'done' => $done,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            'items' => $items,
            'profile' => $profile,
            'trial_days' => $user->trialDaysRemaining(),
            'trial_hours' => method_exists($user, 'trialHoursRemaining') ? $user->trialHoursRemaining() : 0,
            'is_on_trial' => $user->isOnTrial(),
            'can_message' => $user->canSendMessages(),
        ];
    }
}
