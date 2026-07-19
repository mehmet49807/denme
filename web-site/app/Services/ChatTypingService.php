<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ChatTypingService
{
    private const TTL_SECONDS = 5;

    public function ping(int $senderId, int $receiverId): void
    {
        Cache::put($this->key($senderId, $receiverId), 1, self::TTL_SECONDS);
    }

    public function isTyping(int $senderId, int $receiverId): bool
    {
        return Cache::has($this->key($senderId, $receiverId));
    }

    private function key(int $senderId, int $receiverId): string
    {
        return "gk_chat_typing:{$senderId}:{$receiverId}";
    }
}

