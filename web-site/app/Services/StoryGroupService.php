<?php

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Collection;

class StoryGroupService
{
    /** Paketsiz kullanıcılar da mümkün olduğunca tüm hikayeleri görsün. */
    private const DISCOVERY_STORY_LIMIT = 500;

    private const DISCOVERY_GROUP_LIMIT = 150;

    public function __construct(
        private GenderFilterService $genderFilter,
        private StoryService $stories,
    ) {}

    public function loadUserStoryGroup(User $user): ?array
    {
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
        $visibleSubquery = $visibleUserIds !== null
            ? null
            : $this->genderFilter->visibleUsersQuery($viewer);

        if ($visibleUserIds !== null && $visibleUserIds->isEmpty()) {
            return collect();
        }

        $now = now()->toDateTimeString();
        $premiumWith = ['user.premiumSubscriptions' => function ($q) {
            $q->active()->latest('expires_at');
        }];

        try {
            // Önce paket / boost ile sırala — görüntüleme herkese açık, sıralama sadece öne çıkarma.
            $query = Story::active()
                ->with($premiumWith)
                ->join('users', 'users.id', '=', 'stories.user_id')
                ->where('stories.user_id', '!=', $viewer->id)
                ->orderByRaw('CASE WHEN users.boost_until IS NOT NULL AND users.boost_until > ? THEN 0 ELSE 1 END', [$now])
                ->orderByRaw(User::packageTypeOrderSql('users.id'), [$now])
                ->orderByDesc('stories.created_at')
                ->select('stories.*')
                ->limit(self::DISCOVERY_STORY_LIMIT);

            if ($visibleUserIds !== null) {
                $query->whereIn('stories.user_id', $visibleUserIds);
            } else {
                $query->whereIn('stories.user_id', (clone $visibleSubquery)->select('users.id'));
            }

            $stories = $query->get();
        } catch (\Throwable) {
            // Sıralama sorgusu düşerse paketsiz kullanıcılar yine tüm hikayeleri görsün.
            $query = Story::active()
                ->with($premiumWith)
                ->where('user_id', '!=', $viewer->id)
                ->latest()
                ->limit(self::DISCOVERY_STORY_LIMIT);

            if ($visibleUserIds !== null) {
                $query->whereIn('user_id', $visibleUserIds);
            } else {
                $query->whereIn('user_id', (clone $visibleSubquery)->select('users.id'));
            }

            $stories = $query->get();
        }

        return $stories
            ->filter(fn ($story) => $story->user)
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
}
