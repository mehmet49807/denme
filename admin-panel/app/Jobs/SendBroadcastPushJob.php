<?php

namespace App\Jobs;

use App\Models\AdminBroadcast;
use App\Services\FcmPushService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendBroadcastPushJob
{
    use Dispatchable;

    public function __construct(public int $broadcastId) {}

    public function handle(FcmPushService $fcm): void
    {
        $broadcast = AdminBroadcast::find($this->broadcastId);
        if (! $broadcast) {
            return;
        }

        try {
            $pushCount = $fcm->sendBroadcastPushChunked($broadcast);
            Log::info('Broadcast push job finished.', [
                'broadcast_id' => $broadcast->id,
                'push_count' => $pushCount,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Broadcast push job failed.', [
                'broadcast_id' => $broadcast->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
