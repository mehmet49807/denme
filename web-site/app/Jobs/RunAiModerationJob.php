<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\Story;
use App\Models\User;
use App\Services\AiModerationService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class RunAiModerationJob
{
    use Dispatchable;

    public function __construct(
        private string $type,
        private int $id,
    ) {}

    public function handle(AiModerationService $moderation): void
    {
        try {
            match ($this->type) {
                'message' => $this->moderateMessage($moderation),
                'post' => $this->moderatePost($moderation),
                'story' => $this->moderateStory($moderation),
                'report' => $this->moderateReport($moderation),
                'profile' => $this->moderateProfile($moderation),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning('RunAiModerationJob failed', [
                'type' => $this->type,
                'id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function moderateMessage(AiModerationService $moderation): void
    {
        $message = Message::find($this->id);
        if ($message) {
            $moderation->moderateMessage($message);
        }
    }

    private function moderatePost(AiModerationService $moderation): void
    {
        $post = Post::find($this->id);
        if ($post) {
            $moderation->moderatePost($post);
        }
    }

    private function moderateStory(AiModerationService $moderation): void
    {
        $story = Story::find($this->id);
        if ($story) {
            $moderation->moderateStory($story);
        }
    }

    private function moderateReport(AiModerationService $moderation): void
    {
        $report = Report::find($this->id);
        if ($report) {
            $moderation->moderateReport($report);
        }
    }

    private function moderateProfile(AiModerationService $moderation): void
    {
        $user = User::find($this->id);
        if ($user) {
            $moderation->moderateUserProfile($user);
        }
    }
}
