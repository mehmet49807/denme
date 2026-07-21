<?php

namespace App\Jobs;

use App\Services\FcmPushService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendFcmPushJob
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    public function handle(FcmPushService $fcm): void
    {
        try {
            $fcm->sendToUserId($this->userId, $this->title, $this->body, $this->data);
        } catch (\Throwable $e) {
            Log::warning('SendFcmPushJob failed.', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
