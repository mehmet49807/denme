<?php

namespace App\Support;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;

final class SidebarBadgeCounts
{
    private const TTL_SECONDS = 20;

    /** @return array{notifications: int, messages: int} */
    public static function forUser(User $user): array
    {
        return Cache::remember(
            'sidebar_badges:'.$user->id,
            now()->addSeconds(self::TTL_SECONDS),
            static function () use ($user): array {
                try {
                    $notifications = app(NotificationService::class);

                    return [
                        'notifications' => $notifications->unreadNotificationsCount($user),
                        'messages' => $notifications->unreadMessageCount($user),
                    ];
                } catch (\Throwable) {
                    return ['notifications' => 0, 'messages' => 0];
                }
            }
        );
    }

    public static function forget(int $userId): void
    {
        try {
            Cache::forget('sidebar_badges:'.$userId);
        } catch (\Throwable) {
            //
        }
    }
}
