<?php

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class StoryService
{
    public const TTL_HOURS = 24;

    public function __construct(private MediaUploadService $mediaUpload) {}

    public function expiresAtForNewStory(?Carbon $from = null): Carbon
    {
        return ($from ?? now())->copy()->addHours(self::TTL_HOURS);
    }

    public function createForUser(User $user, string $mediaUrl, string $mediaType): Story
    {
        return Story::create([
            'user_id' => $user->id,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'expires_at' => $this->expiresAtForNewStory(),
        ]);
    }

    public function backfillMissingExpiresAt(): int
    {
        $updated = 0;

        Story::query()
            ->whereNull('expires_at')
            ->whereNotNull('created_at')
            ->orderBy('id')
            ->chunkById(100, function ($stories) use (&$updated) {
                foreach ($stories as $story) {
                    $story->forceFill([
                        'expires_at' => $story->created_at->copy()->addHours(self::TTL_HOURS),
                    ])->saveQuietly();
                    $updated++;
                }
            });

        return $updated;
    }

    public function expiredStoriesQuery(): Builder
    {
        return Story::query()->where(function (Builder $query) {
            $query->where('stories.expires_at', '<=', now())
                ->orWhere(function (Builder $nested) {
                    $nested->whereNull('stories.expires_at')
                        ->where('stories.created_at', '<=', now()->subHours(self::TTL_HOURS));
                });
        });
    }

    public function purgeExpired(): int
    {
        $this->backfillMissingExpiresAt();

        $deleted = 0;

        $this->expiredStoriesQuery()
            ->orderBy('id')
            ->chunkById(50, function ($stories) use (&$deleted) {
                foreach ($stories as $story) {
                    $this->mediaUpload->deleteByUrl($story->media_url);
                    $story->delete();
                    $deleted++;
                }
            });

        return $deleted;
    }

    public function purgeExpiredIfNeeded(): void
    {
        $shouldPurge = true;

        try {
            $lastPurgedAt = (int) Cache::get('stories_last_purged_at', 0);
            $shouldPurge = $lastPurgedAt <= now()->subMinutes(15)->timestamp;
        } catch (\Throwable) {
            // Cache kullanılamıyorsa yine de temizle.
        }

        if (! $shouldPurge) {
            return;
        }

        $this->purgeExpired();

        try {
            Cache::put('stories_last_purged_at', now()->timestamp, 900);
        } catch (\Throwable) {
            // ignore
        }
    }
}
