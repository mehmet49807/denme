<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Admin panel stub/helper — unread counts for badge cache invalidation paths.
 */
class ConversationService
{
    public function unreadMessageCount(User $user): int
    {
        try {
            if (! Schema::hasTable('messages')) {
                return 0;
            }

            return (int) Message::query()
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
