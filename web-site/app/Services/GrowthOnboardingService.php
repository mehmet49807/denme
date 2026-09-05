<?php

namespace App\Services;

use App\Models\Like;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class GrowthOnboardingService
{
    /** İlk 72 saat aktivasyon penceresi */
    public const WINDOW_HOURS = 72;

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

        $checklist = collect($this->checklist($user))
            ->reject(fn (array $item) => ($item['key'] ?? '') === 'invite');

        return $checklist->contains(fn (array $item) => ! $item['done']);
    }

    /**
     * @return list<array{key: string, label: string, done: bool, href: string, step?: int, hint?: string}>
     */
    public function checklist(User $user): array
    {
        $hasPhoto = filled($user->profile_photo_url);
        $likeCount = 0;
        $messageCount = 0;
        $profileLikeCount = 0;
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

        try {
            if (class_exists(\App\Models\ProfileLike::class) && Schema::hasTable('profile_likes')) {
                $profileLikeCount = (int) \App\Models\ProfileLike::query()->where('liker_id', $user->id)->count();
            }
        } catch (\Throwable) {
            $profileLikeCount = 0;
        }

        $items = [
            [
                'key' => 'photo',
                'label' => 'Profil fotoğrafı ekle',
                'done' => $hasPhoto,
                'href' => route('profile'),
                'step' => 1,
            ],
            [
                'key' => 'email',
                'label' => 'E-posta adresini doğrula',
                'done' => filled($user->email_verified_at),
                'href' => route('profile'),
                'step' => 1,
            ],
            [
                'key' => 'profile_score',
                'label' => 'Profilini %70 tamamla (şu an %'.$profile['percent'].')',
                'done' => $profile['percent'] >= 70,
                'href' => route('profile'),
                'step' => 2,
            ],
            [
                'key' => 'like',
                'label' => '3 gönderiyi beğen',
                'done' => $likeCount >= 3,
                'href' => route('feed'),
                'step' => 2,
            ],
            [
                'key' => 'profile_like',
                'label' => '2 profili beğen',
                'done' => $profileLikeCount >= 2,
                'href' => route('search'),
                'step' => 3,
            ],
            [
                'key' => 'message',
                'label' => '1 kişiye ilk mesajını gönder',
                'done' => $messageCount >= 1,
                'href' => route('matches.index'),
                'step' => 3,
            ],
            [
                'key' => 'invite',
                'label' => 'Arkadaşını davet et',
                'done' => false,
                'href' => route('referral'),
                'step' => 3,
            ],
        ];

        if ($user->gender === 'female') {
            array_unshift($items, [
                'key' => 'women_perk',
                'label' => 'Kimler baktı ve mesajlaşma sende ücretsiz',
                'done' => true,
                'href' => route('profile'),
                'step' => 1,
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
                'step' => 1,
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
        $coreItems = collect($items)->reject(fn (array $item) => in_array($item['key'] ?? '', ['invite', 'women_perk', 'trial'], true));
        $next = $coreItems->first(fn (array $item) => ! $item['done']);

        return [
            'done' => $done,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            'items' => $items,
            'next' => $next,
            'profile' => $profile,
            'trial_days' => $user->trialDaysRemaining(),
            'trial_hours' => method_exists($user, 'trialHoursRemaining') ? $user->trialHoursRemaining() : 0,
            'is_on_trial' => $user->isOnTrial(),
            'can_message' => $user->canSendMessages(),
        ];
    }
}
