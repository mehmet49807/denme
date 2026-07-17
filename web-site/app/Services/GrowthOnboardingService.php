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

        $checklist = $this->checklist($user);

        return collect($checklist)->contains(fn (array $item) => ! $item['done']);
    }

    /**
     * @return list<array{key: string, label: string, done: bool, href: string}>
     */
    public function checklist(User $user): array
    {
        $hasPhoto = filled($user->profile_photo_url);
        $likeCount = 0;
        $messageCount = 0;

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
                'key' => 'like',
                'label' => '3 gönderiyi beğen',
                'done' => $likeCount >= 3,
                'href' => route('feed'),
            ],
            [
                'key' => 'message',
                'label' => '1 kişiye mesaj gönder',
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
            array_unshift($items, [
                'key' => 'trial',
                'label' => '3 gün deneme: hikâye + mesaj açık',
                'done' => $user->isOnTrial() || $user->isPremium(),
                'href' => route('premium'),
            ]);
        }

        try {
            $inviteDone = \App\Models\Referral::query()->where('referrer_id', $user->id)->exists();
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

        return [
            'done' => $done,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            'items' => $items,
        ];
    }
}
