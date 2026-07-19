<?php

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Collection;

class StoryGroupService
{
    private const DISCOVERY_STORY_LIMIT = 160;

    private const DISCOVERY_GROUP_LIMIT = 30;

    public function __construct(
        private GenderFilterService $genderFilter,
        private StoryService $stories,
    ) {}

    public function loadUserStoryGroup(User $user): ?array
    {
        $this->ensureFreshStories();

        $stories = Story::active()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        if ($stories->isEmpty()) {
            return null;
        }

        return $this->formatStoryGroup($user, $stories);
    }

    public function loadOwnStoryGroup(User $viewer): ?array
    {
        $this->ensureFreshStories();

        $ownStories = Story::active()
            ->where('user_id', $viewer->id)
            ->latest()
            ->get();

        if ($ownStories->isEmpty()) {
            return null;
        }

        $group = $this->formatStoryGroup($viewer, $ownStories);
        $group['is_own'] = true;

        return $group;
    }

    public function loadDiscoveryGroups(User $viewer, ?Collection $visibleUserIds = null): Collection
    {
        $this->ensureFreshStories();

        $visibleUserIds ??= $this->genderFilter->visibleUserIds($viewer);

        if ($visibleUserIds->isEmpty()) {
            return collect();
        }

        $now = now()->toDateTimeString();

        // Önce paket / boost ile sırala, sonra limit — Platinum hikayeleri aday setinden düşmesin.
        $stories = Story::active()
            ->with('user')
            ->join('users', 'users.id', '=', 'stories.user_id')
            ->where('stories.user_id', '!=', $viewer->id)
            ->whereIn('stories.user_id', $visibleUserIds)
            ->orderByRaw('CASE WHEN users.boost_until IS NOT NULL AND users.boost_until > ? THEN 0 ELSE 1 END', [$now])
            ->orderByRaw(User::packageTypeOrderSql('users.id'), [$now])
            ->orderByDesc('stories.created_at')
            ->select('stories.*')
            ->limit(self::DISCOVERY_STORY_LIMIT)
            ->get();

        return $stories
            ->groupBy('user_id')
            ->take(self::DISCOVERY_GROUP_LIMIT)
            ->map(function ($userStories) {
                $user = $userStories->first()->user;

                return $this->formatStoryGroup($user, $userStories);
            })
            ->values();
    }

    public function formatStoryGroup(User $user, $stories): array
    {
        return [
            'user_id' => $user->id,
            'username' => $user->username,
            'profile_url' => rtrim((string) config('app.site_url'), '/').'/users/'.$user->username,
            'profile_photo_url' => $user->profile_photo_url,
            'is_online' => $user->isOnline(),
            'is_own' => false,
            'package_type' => $user->activePackageType(),
            'is_featured' => $user->packageRank() >= 2 || $user->isBoosted(),
            'items' => $stories->map(fn ($story) => [
                'id' => $story->id,
                'media_url' => $story->media_url,
                'media_type' => $story->is_video ? 'video' : 'image',
            ])->values()->all(),
        ];
    }

    private function ensureFreshStories(): void
    {
        try {
            $this->stories->purgeExpiredIfNeeded();
        } catch (\Throwable) {
            // Hikaye temizliği başarısız olsa da yanıtı kesme.
        }
    }
}
