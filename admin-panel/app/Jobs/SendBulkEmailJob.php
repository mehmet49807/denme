<?php

namespace App\Jobs;

use App\Services\UserMailService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendBulkEmailJob
{
    use Dispatchable;

    public function __construct(
        public string $target,
        public ?string $singleEmail,
        public string $subject,
        public string $body,
        public string $templateKey,
        public int $adminId,
    ) {}

    public function handle(UserMailService $mail): void
    {
        try {
            $recipients = $mail->resolveRecipients($this->target, $this->singleEmail);
            $result = $mail->sendBulk(
                $recipients,
                $this->subject,
                $this->body,
                $this->templateKey,
                $this->adminId,
            );

            Log::info('Bulk email job finished.', [
                'target' => $this->target,
                'admin_id' => $this->adminId,
                'sent' => $result['sent'] ?? 0,
                'failed' => $result['failed'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Bulk email job failed.', [
                'target' => $this->target,
                'admin_id' => $this->adminId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
