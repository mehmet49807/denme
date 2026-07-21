<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MessageService
{
    public const ADMIN_RETENTION_DAYS = 30;

    public function visibleToUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where(function (Builder $inner) use ($userId) {
                $inner->where('sender_id', $userId)
                    ->whereNull('hidden_for_sender_at');
            })->orWhere(function (Builder $inner) use ($userId) {
                $inner->where('receiver_id', $userId)
                    ->whereNull('hidden_for_receiver_at');
            });
        });
    }

    public function hideForUser(Message $message, User $viewer): bool
    {
        $saved = false;

        if ((int) $message->sender_id === (int) $viewer->id) {
            if ($message->hidden_for_sender_at) {
                return true;
            }
            $message->hidden_for_sender_at = now();
            $saved = $message->save();
        } elseif ((int) $message->receiver_id === (int) $viewer->id) {
            if ($message->hidden_for_receiver_at) {
                return true;
            }
            $message->hidden_for_receiver_at = now();
            $saved = $message->save();
        }

        if ($saved) {
            try {
                app(ConversationService::class)->forgetConversationsCache((int) $message->sender_id);
                app(ConversationService::class)->forgetConversationsCache((int) $message->receiver_id);
            } catch (\Throwable) {
                //
            }
        }

        return $saved;
    }

    public function purgeOlderThanRetention(): int
    {
        return Message::query()
            ->where('created_at', '<', now()->subDays(self::ADMIN_RETENTION_DAYS))
            ->delete();
    }

    public function purgeRetentionIfNeeded(): void
    {
        $shouldPurge = true;

        try {
            $lastPurgedAt = (int) \Illuminate\Support\Facades\Cache::get('messages_retention_last_purged_at', 0);
            $shouldPurge = $lastPurgedAt <= now()->subHours(6)->timestamp;
        } catch (\Throwable) {
            // ignore
        }

        if (! $shouldPurge) {
            return;
        }

        $this->purgeOlderThanRetention();

        try {
            \Illuminate\Support\Facades\Cache::put('messages_retention_last_purged_at', now()->timestamp, 21600);
        } catch (\Throwable) {
            // ignore
        }
    }
}

