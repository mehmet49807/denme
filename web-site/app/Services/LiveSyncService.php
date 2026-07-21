<?php

namespace App\Services;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LiveSyncService
{
    public function __construct(
        private GenderFilterService $genderFilter,
        private StoryGroupService $storyGroups,
    ) {}

    /**
     * @param  list<int>  $postIds
     * @return list<array{id:int,likes_count:int,is_liked:bool,like_url:string}>
     */
    public function postUpdates(User $viewer, array $postIds): array
    {
        $postIds = array_values(array_unique(array_filter(array_map('intval', $postIds))));
        if ($postIds === []) {
            return [];
        }

        $posts = Post::query()
            ->whereIn('id', $postIds)
            ->get(['id', 'likes_count']);

        if ($posts->isEmpty()) {
            return [];
        }

        $liked = Like::query()
            ->where('user_id', $viewer->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('post_id')
            ->all();

        return $posts->map(fn (Post $post) => [
            'id' => (int) $post->id,
            'likes_count' => (int) ($post->likes_count ?? 0),
            'is_liked' => in_array($post->id, $liked, true),
            'like_url' => route('posts.like', $post),
        ])->values()->all();
    }

    public function parseSince(mixed $raw): ?Carbon
    {
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return Collection<int, array{id:int,created_at:?string}>
     */
    public function newFeedPosts(User $viewer, ?Carbon $since): Collection
    {
        if (! $since) {
            return collect();
        }

        $visible = $this->genderFilter->visibleUsersQuery($viewer);

        return User::applyContentRanking(
            Post::query()
                ->with(['user.premiumSubscriptions' => fn ($q) => $q->active()->latest('expires_at')])
                ->where('posts.is_active', true)
                ->where('posts.created_at', '>', $since)
                ->whereIn('posts.user_id', (clone $visible)->select('users.id'))
        )
            ->limit(20)
            ->get()
            ->map(fn (Post $post) => [
                'id' => (int) $post->id,
                'created_at' => $post->created_at?->toIso8601String(),
            ]);
    }

    /**
     * @return Collection<int, array{id:int,created_at:?string}>
     */
    public function newProfilePosts(User $viewer, User $profileUser, ?Carbon $since): Collection
    {
        if (! $since) {
            return collect();
        }

        return Post::query()
            ->where('user_id', $profileUser->id)
            ->where('is_active', true)
            ->where('created_at', '>', $since)
            ->latest('created_at')
            ->limit(24)
            ->get(['id', 'created_at'])
            ->map(fn (Post $post) => [
                'id' => (int) $post->id,
                'created_at' => $post->created_at?->toIso8601String(),
            ]);
    }

    /**
     * @return array{own:?array,groups:list<array>}
     */
    public function storiesPayload(User $viewer): array
    {
        $own = $this->storyGroups->loadOwnStoryGroup($viewer);
        $groups = $this->storyGroups->loadDiscoveryGroups($viewer);

        return [
            'own' => $own,
            'groups' => $groups->values()->all(),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function discoveryUsers(User $viewer): Collection
    {
        return User::applyDiscoveryRanking(
            User::query()
                ->where('role', 'user')
                ->where('is_banned', false)
                ->where('id', '!=', $viewer->id)
                ->where(function ($q) use ($viewer) {
                    $this->genderFilter->applyDiscoveryFilters($q, $viewer);
                })
                ->with(['premiumSubscriptions' => fn ($q) => $q->active()->latest('expires_at')])
                ->withCount(['posts' => fn ($q) => $q->where('is_active', true)])
        )
            ->limit(48)
            ->get();
    }
}
