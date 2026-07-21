<?php

namespace App\Services;

use App\Jobs\SendFcmPushJob;
use App\Models\AdminBroadcast;
use App\Models\Like;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use App\Models\UserBroadcastRead;
use App\Models\UserNotification;
use App\Support\SidebarBadgeCounts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    private static ?bool $hasUserNotificationsTable = null;

    private function userNotificationsTableExists(): bool
    {
        if (self::$hasUserNotificationsTable === null) {
            try {
                self::$hasUserNotificationsTable = \Illuminate\Support\Facades\Schema::hasTable('user_notifications');
            } catch (\Throwable) {
                self::$hasUserNotificationsTable = false;
            }
        }

        return self::$hasUserNotificationsTable;
    }
    public function allForUser(User $user): Collection
    {
        $broadcasts = $this->broadcastsForUser($user)->map(fn ($item) => array_merge($item, [
            'type' => 'broadcast',
            'actor_username' => null,
            'profile_url' => null,
            'post_id' => null,
        ]));

        $likes = $this->safeUserNotificationsForUser($user);

        return $broadcasts
            ->toBase()
            ->merge($likes->toBase())
            ->sortByDesc(fn ($item) => $this->notificationTimestamp($item['created_at'] ?? null))
            ->values();
    }

    public function broadcastsForUser(User $user): Collection
    {
        $readMap = UserBroadcastRead::where('user_id', $user->id)
            ->whereNotNull('read_at')
            ->pluck('read_at', 'broadcast_id');

        return AdminBroadcast::forUser($user)
            ->recent()
            ->with('admin')
            ->latest()
            ->get()
            ->map(function (AdminBroadcast $broadcast) use ($readMap) {
                return [
                    'id' => 'broadcast-'.$broadcast->id,
                    'title' => $broadcast->title,
                    'message_text' => $broadcast->message_text,
                    'created_at' => $broadcast->created_at,
                    'is_read' => $readMap->has($broadcast->id),
                ];
            });
    }

    public function userNotificationsForUser(User $user): Collection
    {
        return UserNotification::with(['actor', 'post', 'message'])
            ->where('user_id', $user->id)
            ->recent()
            ->latest('created_at')
            ->get()
            ->map(fn (UserNotification $notification) => $this->mapUserNotification($notification));
    }

    /** @deprecated Use userNotificationsForUser() */
    public function likeNotificationsForUser(User $user): Collection
    {
        return $this->userNotificationsForUser($user)
            ->filter(fn (array $item) => ($item['type'] ?? '') === UserNotification::TYPE_POST_LIKE)
            ->values();
    }

    private function safeUserNotificationsForUser(User $user): Collection
    {
        try {
            if (!$this->userNotificationsTableExists()) {
                return collect();
            }

            return $this->userNotificationsForUser($user);
        } catch (\Throwable) {
            return collect();
        }
    }

    private function safeLikeNotificationsForUser(User $user): Collection
    {
        return $this->safeUserNotificationsForUser($user);
    }

    private function mapUserNotification(UserNotification $notification): array
    {
        if ($notification->type === UserNotification::TYPE_NEW_MESSAGE) {
            return $this->mapMessageNotification($notification);
        }

        if ($notification->type === UserNotification::TYPE_REPORT_UPDATE) {
            return $this->mapReportNotification($notification);
        }

        if ($notification->type === UserNotification::TYPE_MODERATION) {
            return $this->mapModerationNotification($notification);
        }

        if ($notification->type === UserNotification::TYPE_ADMIN_NOTICE) {
            return $this->mapAdminNoticeNotification($notification);
        }

        return $this->mapLikeNotification($notification);
    }

    private function mapMessageNotification(UserNotification $notification): array
    {
        $actorName = $notification->actor?->username ?? 'Bir üye';
        $profileUrl = $notification->actor?->username
            ? url('/users/'.$notification->actor->username)
            : null;
        $messagesUrl = $notification->actor?->username
            ? route('messages.show', $notification->actor->username)
            : route('messages.index');

        return [
            'id' => 'message-'.$notification->id,
            'type' => UserNotification::TYPE_NEW_MESSAGE,
            'title' => 'Yeni mesaj',
            'message_text' => $actorName.' size mesaj gönderdi.',
            'created_at' => $notification->created_at,
            'is_read' => $notification->read_at !== null,
            'actor_id' => $notification->actor_id,
            'actor_username' => $notification->actor?->username,
            'profile_url' => $profileUrl,
            'messages_url' => $messagesUrl,
            'post_id' => null,
            'message_id' => $notification->message_id,
        ];
    }

    private function mapLikeNotification(UserNotification $notification): array
    {
        $actorName = $notification->actor?->username ?? 'Bir üye';
        $profileUrl = $notification->actor?->username
            ? url('/users/'.$notification->actor->username)
            : null;

        return [
            'id' => 'like-'.$notification->id,
            'type' => UserNotification::TYPE_POST_LIKE,
            'title' => 'Gönderiniz beğenildi',
            'message_text' => $actorName.' gönderinizi beğendi.',
            'created_at' => $notification->created_at,
            'is_read' => $notification->read_at !== null,
            'actor_id' => $notification->actor_id,
            'actor_username' => $notification->actor?->username,
            'profile_url' => $profileUrl,
            'post_id' => $notification->post_id,
        ];
    }

    private function mapReportNotification(UserNotification $notification): array
    {
        return [
            'id' => 'report-'.$notification->id,
            'type' => UserNotification::TYPE_REPORT_UPDATE,
            'title' => 'Şikayetiniz incelendi',
            'message_text' => $notification->body ?? '',
            'created_at' => $notification->created_at,
            'is_read' => $notification->read_at !== null,
            'actor_id' => $notification->actor_id,
            'actor_username' => null,
            'profile_url' => null,
            'post_id' => null,
            'report_id' => $notification->report_id,
        ];
    }

    private function mapModerationNotification(UserNotification $notification): array
    {
        return [
            'id' => 'moderation-'.$notification->id,
            'type' => UserNotification::TYPE_MODERATION,
            'title' => 'Topluluk kuralları uyarısı',
            'message_text' => $notification->body ?? '',
            'created_at' => $notification->created_at,
            'is_read' => $notification->read_at !== null,
            'actor_id' => null,
            'actor_username' => null,
            'profile_url' => null,
            'post_id' => null,
        ];
    }

    private function mapAdminNoticeNotification(UserNotification $notification): array
    {
        $body = trim((string) ($notification->body ?? ''));
        $title = 'Yönetim bildirimi';
        $text = $body;

        if (str_contains($body, "\n")) {
            [$first, $rest] = explode("\n", $body, 2);
            $title = trim($first) !== '' ? trim($first) : $title;
            $text = trim($rest);
        }

        return [
            'id' => 'admin-'.$notification->id,
            'type' => UserNotification::TYPE_ADMIN_NOTICE,
            'title' => $title,
            'message_text' => $text !== '' ? $text : $body,
            'created_at' => $notification->created_at,
            'is_read' => $notification->read_at !== null,
            'actor_id' => $notification->actor_id,
            'actor_username' => null,
            'profile_url' => null,
            'post_id' => null,
        ];
    }

    public function notifyModerationViolation(User $user, string $reason, string $contentType): void
    {
        try {
            if (! $this->userNotificationsTableExists()) {
                return;
            }

            $typeLabel = match ($contentType) {
                'message' => 'mesaj',
                'post' => 'gönderi',
                'story' => 'hikaye',
                'profile' => 'profil',
                default => 'içerik',
            };

            $body = 'Güvenlik denetimi: '.$typeLabel.' içeriğiniz topluluk kurallarına aykırı bulundu. '.$reason;

            UserNotification::create([
                'user_id' => $user->id,
                'type' => UserNotification::TYPE_MODERATION,
                'body' => $body,
                'created_at' => now(),
            ]);

            $this->forgetSidebarBadges($user->id);

            $this->pushToUser(
                $user,
                'Topluluk kuralları uyarısı',
                $body,
                ['type' => UserNotification::TYPE_MODERATION]
            );
        } catch (\Throwable) {
            //
        }
    }

    public function notifyReportReviewed(
        Report $report,
        User $admin,
        string $previousNotes,
        string $previousStatus,
    ): void {
        try {
            if (! $this->userNotificationsTableExists()) {
                return;
            }

            $report->loadMissing('reporter');
            $reporter = $report->reporter;
            $newNotes = trim((string) ($report->admin_notes ?? ''));
            $newStatus = (string) $report->status;

            if (! $reporter || $reporter->id === $admin->id || $newNotes === '') {
                return;
            }

            if ($newNotes === trim($previousNotes) && $newStatus === $previousStatus) {
                return;
            }

            $statusLabel = $this->reportStatusLabel($newStatus);
            $body = 'Durum: '.$statusLabel.'. Yönetici notu: '.$newNotes;

            UserNotification::create([
                'user_id' => $reporter->id,
                'actor_id' => $admin->id,
                'type' => UserNotification::TYPE_REPORT_UPDATE,
                'report_id' => $report->id,
                'body' => $body,
                'created_at' => now(),
            ]);

            $this->forgetSidebarBadges($reporter->id);

            $this->pushToUser(
                $reporter,
                'Şikayetiniz incelendi',
                $body,
                [
                    'type' => UserNotification::TYPE_REPORT_UPDATE,
                    'report_id' => (string) $report->id,
                ]
            );
        } catch (\Throwable) {
            //
        }
    }

    private function reportStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Beklemede',
            'reviewed' => 'İncelendi',
            'resolved' => 'Çözüldü',
            'dismissed' => 'Reddedildi',
            default => $status,
        };
    }

    private function notificationTimestamp(mixed $value): int
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_string($value) && $value !== '') {
            return (int) strtotime($value);
        }

        return 0;
    }

    public function notifyPostLiked(Like $like): void
    {
        try {
            $like->loadMissing(['post', 'user']);
            $post = $like->post;
            $liker = $like->user;

            if (!$post || !$liker || $post->user_id === $liker->id) {
                return;
            }

            if ($this->userNotificationsTableExists()) {
                try {
                    UserNotification::create([
                        'user_id' => $post->user_id,
                        'actor_id' => $liker->id,
                        'type' => UserNotification::TYPE_POST_LIKE,
                        'post_id' => $post->id,
                        'like_id' => $like->id,
                        'created_at' => now(),
                    ]);
                    $this->forgetSidebarBadges($post->user_id);
                } catch (\Throwable) {
                    // Uygulama içi bildirim olmasa da FCM gitsin.
                }
            }

            $receiver = $post->user ?? User::query()->find($post->user_id);
            if ($receiver) {
                $this->pushToUser(
                    $receiver,
                    'Gönderiniz beğenildi',
                    $liker->username.' gönderinizi beğendi.',
                    [
                        'type' => 'post_like',
                        'actor_id' => (string) $liker->id,
                        'actor_username' => (string) $liker->username,
                        'post_id' => (string) $post->id,
                    ]
                );
            }
        } catch (\Throwable) {
            // Beğeni kaydı başarılı kalsın; bildirim oluşmazsa akış devam etsin.
        }
    }

    public function notifyNewMessage(Message $message): void
    {
        try {
            $message->loadMissing(['sender', 'receiver']);
            $sender = $message->sender;
            $receiver = $message->receiver;

            if (!$sender || !$receiver || $sender->id === $receiver->id) {
                return;
            }

            if ($this->userNotificationsTableExists()) {
                try {
                    UserNotification::create([
                        'user_id' => $receiver->id,
                        'actor_id' => $sender->id,
                        'type' => UserNotification::TYPE_NEW_MESSAGE,
                        'message_id' => $message->id,
                        'created_at' => now(),
                    ]);
                    $this->forgetSidebarBadges($receiver->id);
                } catch (\Throwable) {
                    // Uygulama içi bildirim olmasa da FCM gitsin.
                }
            }

            $this->pushToUser(
                $receiver,
                'Yeni mesaj',
                $sender->username.' size mesaj gönderdi.',
                [
                    'type' => 'new_message',
                    'actor_id' => (string) $sender->id,
                    'actor_username' => (string) $sender->username,
                    'message_id' => (string) $message->id,
                ]
            );
        } catch (\Throwable) {
            // Mesaj kaydı başarılı kalsın.
        }
    }

    public function notifyAdminNotice(User $user, string $title, string $body, array $data = [], ?User $actor = null): void
    {
        try {
            $title = trim($title);
            $body = trim($body);
            if ($title === '' && $body === '') {
                return;
            }

            if ($this->userNotificationsTableExists()) {
                try {
                    $stored = $title !== '' ? ($title.($body !== '' ? "\n".$body : '')) : $body;

                    UserNotification::create([
                        'user_id' => $user->id,
                        'actor_id' => $actor?->id,
                        'type' => UserNotification::TYPE_ADMIN_NOTICE,
                        'body' => $stored,
                        'created_at' => now(),
                    ]);
                    $this->forgetSidebarBadges($user->id);
                } catch (\Throwable) {
                    // Uygulama içi bildirim olmasa da FCM gitsin.
                }
            }

            $this->pushToUser(
                $user,
                $title !== '' ? $title : 'Yönetim bildirimi',
                $body !== '' ? $body : $title,
                array_merge(['type' => UserNotification::TYPE_ADMIN_NOTICE], $data)
            );
        } catch (\Throwable) {
            //
        }
    }

    public function notifySupportReply(\App\Models\SupportTicket $ticket, ?User $admin = null): void
    {
        try {
            $user = null;
            if (! empty($ticket->user_id)) {
                $user = User::query()->find($ticket->user_id);
            }
            if (! $user && ! empty($ticket->email)) {
                $user = User::query()->where('email', $ticket->email)->first();
            }
            if (! $user) {
                return;
            }

            $reply = trim((string) ($ticket->admin_reply ?? ''));
            $this->notifyAdminNotice(
                $user,
                'Destek yanıtı',
                $reply !== '' ? $reply : 'Destek talebinize yanıt verildi.',
                [
                    'type' => 'support_reply',
                    'ticket_id' => (string) $ticket->id,
                ],
                $admin
            );
        } catch (\Throwable) {
            //
        }
    }

    public function notifyPremiumGranted(User $user, string $packageLabel): void
    {
        $this->notifyAdminNotice(
            $user,
            'Premium aktif',
            $packageLabel.' paketiniz hesabınıza tanımlandı.',
            ['type' => 'premium_granted', 'package' => $packageLabel]
        );
    }

    public function notifyPremiumCancelled(User $user): void
    {
        $this->notifyAdminNotice(
            $user,
            'Premium iptal',
            'Aktif premium aboneliğiniz sonlandırıldı.',
            ['type' => 'premium_cancelled']
        );
    }

    public function notifyAccountBanned(User $user, ?string $reason = null): void
    {
        $body = 'Hesabınız kısıtlandı.';
        $reason = trim((string) $reason);
        if ($reason !== '') {
            $body .= ' Gerekçe: '.$reason;
        }

        $this->notifyAdminNotice($user, 'Hesap durumu', $body, ['type' => 'account_banned']);
    }

    public function notifyAccountUnbanned(User $user): void
    {
        $this->notifyAdminNotice(
            $user,
            'Hesap durumu',
            'Hesap kısıtlamanız kaldırıldı. Tekrar giriş yapabilirsiniz.',
            ['type' => 'account_unbanned']
        );
    }

    private function pushToUser(User $user, string $title, string $body, array $data = []): void
    {
        try {
            SendFcmPushJob::dispatchAfterResponse($user->id, $title, $body, $data);
        } catch (\Throwable) {
            try {
                app(FcmPushService::class)->sendToUser($user, $title, $body, $data);
            } catch (\Throwable) {
                //
            }
        }
    }

    public function markMessageNotificationsRead(User $user, int $actorId): void
    {
        try {
            if (!$this->userNotificationsTableExists()) {
                return;
            }

            UserNotification::where('user_id', $user->id)
                ->where('type', UserNotification::TYPE_NEW_MESSAGE)
                ->where('actor_id', $actorId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $this->forgetSidebarBadges($user->id);
        } catch (\Throwable) {
            //
        }
    }

    private function forgetSidebarBadges(int $userId): void
    {
        SidebarBadgeCounts::forget($userId);
    }

    public function unreadBroadcastCount(User $user): int
    {
        $broadcastIds = AdminBroadcast::forUser($user)->recent()->pluck('id');

        if ($broadcastIds->isEmpty()) {
            return 0;
        }

        $readIds = UserBroadcastRead::where('user_id', $user->id)
            ->whereIn('broadcast_id', $broadcastIds)
            ->whereNotNull('read_at')
            ->pluck('broadcast_id');

        return $broadcastIds->diff($readIds)->count();
    }

    public function unreadUserNotificationCount(User $user): int
    {
        try {
            if (!$this->userNotificationsTableExists()) {
                return 0;
            }

            return UserNotification::where('user_id', $user->id)
                ->recent()
                ->whereNull('read_at')
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function unreadNotificationsCount(User $user): int
    {
        return $this->unreadBroadcastCount($user) + $this->unreadUserNotificationCount($user);
    }

    public function unreadMessageCount(User $user): int
    {
        try {
            return app(ConversationService::class)->unreadMessageCount($user);
        } catch (\Throwable) {
            return 0;
        }
    }

    public function markBroadcastRead(User $user, int $broadcastId): void
    {
        $exists = AdminBroadcast::forUser($user)->recent()->where('id', $broadcastId)->exists();

        if (!$exists) {
            return;
        }

        UserBroadcastRead::updateOrCreate(
            ['broadcast_id' => $broadcastId, 'user_id' => $user->id],
            ['read_at' => now()]
        );
    }

    public function markAllBroadcastsRead(User $user): void
    {
        $broadcastIds = AdminBroadcast::forUser($user)->recent()->pluck('id');

        foreach ($broadcastIds as $broadcastId) {
            UserBroadcastRead::updateOrCreate(
                ['broadcast_id' => $broadcastId, 'user_id' => $user->id],
                ['read_at' => now()]
            );
        }
    }

    public function markAllUserNotificationsRead(User $user): void
    {
        try {
            if (!$this->userNotificationsTableExists()) {
                return;
            }

            UserNotification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $this->forgetSidebarBadges($user->id);
        } catch (\Throwable) {
            // Bildirim tablosu hazır değilse sayfa yine açılsın.
        }
    }

    public function markAllRead(User $user): void
    {
        $this->markAllBroadcastsRead($user);
        $this->markAllUserNotificationsRead($user);
    }

    public function itemsSince(User $user, \DateTimeInterface $since): Collection
    {
        $readMap = UserBroadcastRead::where('user_id', $user->id)
            ->whereNotNull('read_at')
            ->pluck('read_at', 'broadcast_id');

        $broadcasts = AdminBroadcast::forUser($user)
            ->recent()
            ->where('created_at', '>', $since)
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (AdminBroadcast $broadcast) use ($readMap) {
                return array_merge([
                    'id' => 'broadcast-'.$broadcast->id,
                    'type' => 'broadcast',
                    'title' => $broadcast->title,
                    'message_text' => $broadcast->message_text,
                    'created_at' => $broadcast->created_at,
                    'is_read' => $readMap->has($broadcast->id),
                    'actor_username' => null,
                    'profile_url' => null,
                    'post_id' => null,
                ], []);
            });

        $userItems = collect();
        if ($this->userNotificationsTableExists()) {
            try {
                $userItems = UserNotification::with(['actor', 'post', 'message'])
                    ->where('user_id', $user->id)
                    ->recent()
                    ->where('created_at', '>', $since)
                    ->latest('created_at')
                    ->limit(20)
                    ->get()
                    ->map(fn (UserNotification $notification) => $this->mapUserNotification($notification));
            } catch (\Throwable) {
                $userItems = collect();
            }
        }

        return $broadcasts
            ->toBase()
            ->merge($userItems->toBase())
            ->sortByDesc(fn ($item) => $this->notificationTimestamp($item['created_at'] ?? null))
            ->values();
    }

    public function purgeExpired(): int
    {
        $deleted = 0;

        $expiredBroadcastIds = AdminBroadcast::where('created_at', '<', now()->subHours(AdminBroadcast::TTL_HOURS))
            ->pluck('id');

        if ($expiredBroadcastIds->isNotEmpty()) {
            UserBroadcastRead::whereIn('broadcast_id', $expiredBroadcastIds)->delete();
            $deleted += AdminBroadcast::whereIn('id', $expiredBroadcastIds)->delete();
        }

        if ($this->userNotificationsTableExists()) {
            try {
                $deleted += UserNotification::where('created_at', '<', now()->subHours(UserNotification::TTL_HOURS))
                    ->delete();
            } catch (\Throwable) {
                // Tablo hazır değilse duyuru temizliği yine tamamlanmış olsun.
            }
        }

        return $deleted;
    }

    /** @return array{broadcast_reads: int, broadcasts: int, user_notifications: int} */
    public function purgeAll(): array
    {
        $stats = [
            'broadcast_reads' => 0,
            'broadcasts' => 0,
            'user_notifications' => 0,
        ];

        try {
            $stats['broadcast_reads'] = UserBroadcastRead::query()->delete();
        } catch (\Throwable) {
            //
        }

        try {
            $stats['broadcasts'] = AdminBroadcast::query()->delete();
        } catch (\Throwable) {
            //
        }

        if ($this->userNotificationsTableExists()) {
            try {
                $stats['user_notifications'] = UserNotification::query()->delete();
            } catch (\Throwable) {
                //
            }
        }

        try {
            Cache::forget('notifications_last_purged_at');
            Cache::forget('broadcasts_last_purged_at');
        } catch (\Throwable) {
            //
        }

        return $stats;
    }

    public function purgeExpiredIfNeeded(): void
    {
        $shouldPurge = true;

        try {
            $lastPurgedAt = (int) Cache::get('notifications_last_purged_at', 0);
            $shouldPurge = $lastPurgedAt <= now()->subMinutes(15)->timestamp;
        } catch (\Throwable) {
            // Sunucuda cache tablosu yoksa veya sürücü hatalıysa yine de temizle.
        }

        if (!$shouldPurge) {
            return;
        }

        $this->purgeExpired();

        try {
            Cache::put('notifications_last_purged_at', now()->timestamp, 900);
        } catch (\Throwable) {
            // Önbellek yazılamazsa site çalışmaya devam etsin.
        }
    }
}

