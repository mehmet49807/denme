<?php

namespace App\Console\Commands;

use App\Jobs\RunAiModerationJob;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Services\AiModerationService;
use Illuminate\Console\Command;

class AiScanPendingCommand extends Command
{
    protected $signature = 'ai:scan-pending {--hours=24 : Son kaç saat taranacak}';

    protected $description = 'Son içerikleri AI ile tarar (mesaj, gönderi, şikayet, profil)';

    public function handle(AiModerationService $moderation): int
    {
        $since = now()->subHours((int) $this->option('hours'));
        $count = 0;

        Message::where('created_at', '>=', $since)
            ->orderByDesc('id')
            ->limit(50)
            ->pluck('id')
            ->each(function (int $id) use (&$count) {
                RunAiModerationJob::dispatch('message', $id);
                $count++;
            });

        Post::where('created_at', '>=', $since)
            ->whereNotNull('caption')
            ->where('caption', '!=', '')
            ->orderByDesc('id')
            ->limit(30)
            ->pluck('id')
            ->each(function (int $id) use (&$count) {
                RunAiModerationJob::dispatch('post', $id);
                $count++;
            });

        Report::where('status', 'pending')
            ->where('created_at', '>=', $since)
            ->orderByDesc('id')
            ->limit(20)
            ->pluck('id')
            ->each(function (int $id) use (&$count) {
                RunAiModerationJob::dispatch('report', $id);
                $count++;
            });

        User::where('role', 'user')
            ->where('created_at', '>=', $since)
            ->orderByDesc('id')
            ->limit(20)
            ->pluck('id')
            ->each(function (int $id) use (&$count) {
                RunAiModerationJob::dispatch('profile', $id);
                $count++;
            });

        $this->info("AI tarama kuyruğa alındı: {$count} öğe.");

        return self::SUCCESS;
    }
}
