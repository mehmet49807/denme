<?php

namespace App\Services;

class RealtimeBroadcastService
{
    public function isEnabled(): bool
    {
        $key = trim((string) config('broadcasting.connections.pusher.key', ''));
        $appId = trim((string) config('broadcasting.connections.pusher.app_id', ''));

        return $key !== '' && $appId !== '';
    }
}
