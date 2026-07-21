<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Log;

class RealtimeBroadcastService
{
    public function isEnabled(): bool
    {
        $key = trim((string) config('broadcasting.connections.pusher.key', ''));
        $appId = trim((string) config('broadcasting.connections.pusher.app_id', ''));

        return $key !== '' && $appId !== '';
    }

    public function postUpdated(Post $post): void
    {
        $this->safeEvent('post.updated', [
            'post_id' => (int) $post->id,
            'user_id' => (int) $post->user_id,
            'likes_count' => (int) ($post->likes_count ?? 0),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function safeEvent(string $event, array $payload = []): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            if (! class_exists(\Pusher\Pusher::class)) {
                return;
            }

            $cfg = config('broadcasting.connections.pusher', []);
            $pusher = new \Pusher\Pusher(
                (string) ($cfg['key'] ?? ''),
                (string) ($cfg['secret'] ?? ''),
                (string) ($cfg['app_id'] ?? ''),
                [
                    'cluster' => (string) ($cfg['options']['cluster'] ?? 'mt1'),
                    'useTLS' => (bool) ($cfg['options']['useTLS'] ?? true),
                ]
            );

            $pusher->trigger('private-gonul-feed', $event, $payload);
        } catch (\Throwable $e) {
            Log::debug('RealtimeBroadcast skipped.', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
