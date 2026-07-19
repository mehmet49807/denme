<?php

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Collection;

class StoryGroupService
{
    private const DISCOVERY_STORY_LIMIT = 120;

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

        $grouped = Story::active()
            ->with('user')
            ->where('user_id', '!=', $viewer->id)
            ->whereIn('user_id', $visibleUserIds)
            ->latest()
            ->limit(self::DISCOVERY_STORY_LIMIT)
            ->get()
            ->groupBy('user_id');

        return $grouped
            ->sortByDesc(function ($userStories) {
                $user = $userStories->first()?->user;
                if (! $user) {
                    return 0;
                }

                // Platinum / Gold hikaye şeridinde öne çıkar
                return ($user->contentVisibilityScore() * 1_000_000_000)
                    + (int) optional($userStories->first())->created_at?->getTimestamp();
            })
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
