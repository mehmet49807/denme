<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\LiveSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSyncController extends Controller
{
    public function __construct(
        private LiveSyncService $liveSync,
        private ConversationService $conversations,
    ) {}

    public function sync(Request $request): JsonResponse
    {
        $viewer = $request->user();
        $postIds = $this->parsePostIds($request->query('post_ids'));

        $data = [
            'post_updates' => $this->liveSync->postUpdates($viewer, $postIds),
            'server_time' => now()->toIso8601String(),
        ];

        if ($request->has('feed_since')) {
            $since = $this->liveSync->parseSince($request->query('feed_since'));
            $newPosts = $this->liveSync->newFeedPosts($viewer, $since);
            $data['feed_new_posts'] = $newPosts->values()->all();

            if ($newPosts->isNotEmpty()) {
                $likedPostIds = Like::where('user_id', $viewer->id)
                    ->whereIn('post_id', $newPosts->pluck('id'))
                    ->pluck('post_id')
                    ->all();

                $postModels = Post::with('user')
                    ->whereIn('id', $newPosts->pluck('id'))
                    ->get()
                    ->keyBy('id');

                $html = '';
                foreach ($newPosts as $index => $postArray) {
                    $post = $postModels->get($postArray['id']);
                    if (! $post) {
                        continue;
                    }
                    $html .= view('partials.feed-post-card', [
                        'post' => $post,
                        'viewer' => $viewer,
                        'isLiked' => in_array($post->id, $likedPostIds, true),
                        'loopIndex' => $index,
                    ])->render();
                }
                $data['feed_new_html'] = $html;
            }
        }

        if ($request->boolean('stories')) {
            $data['stories'] = $this->liveSync->storiesPayload($viewer);
        }

        if ($username = $request->query('profile_username')) {
            $profileUser = User::where('username', $username)->where('role', 'user')->first();
            if ($profileUser && $this->canViewProfile($viewer, $profileUser)) {
                $since = $this->liveSync->parseSince($request->query('profile_since'));
                $newPosts = $this->liveSync->newProfilePosts($viewer, $profileUser, $since);
                $data['profile_new_posts'] = $newPosts->values()->all();

                if ($newPosts->isNotEmpty()) {
                    $isOwnProfile = $profileUser->id === $viewer->id;
                    $likedPostIds = Like::where('user_id', $viewer->id)
                        ->whereIn('post_id', $newPosts->pluck('id'))
                        ->pluck('post_id')
                        ->all();

                    $postModels = Post::whereIn('id', $newPosts->pluck('id'))->get()->keyBy('id');

                    $html = '';
                    foreach ($newPosts as $index => $postArray) {
                        $post = $postModels->get($postArray['id']);
                        if (! $post) {
                            continue;
                        }
                        $html .= view('partials.profile-post-grid-item', [
                            'post' => $post,
                            'profileUser' => $profileUser,
                            'viewer' => $viewer,
                            'isOwnProfile' => $isOwnProfile,
                            'isLiked' => in_array($post->id, $likedPostIds, true),
                            'loopIndex' => $index,
                        ])->render();
                    }
                    $data['profile_new_html'] = $html;
                }
            }
        }

        if ($request->boolean('users')) {
            $users = $this->liveSync->discoveryUsers($viewer);
            $data['users'] = [
                'users' => $users->map(fn (User $user) => array_merge(
                    $user->toPublicArray(),
                    ['posts_count' => $user->posts_count],
                ))->values()->all(),
                'total' => $users->count(),
            ];
            $data['users_html'] = view('partials.users-browse-grid-items', [
                'users' => $users,
            ])->render();
        }

        if ($request->boolean('premium')) {
            $active = $viewer->premiumSubscriptions()
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->latest('expires_at')
                ->first();

            $data['premium'] = [
                'is_premium' => $viewer->isPremium(),
                'is_on_trial' => $viewer->isOnTrial(),
                'trial_days_remaining' => $viewer->trialDaysRemaining(),
                'package_type' => $active?->package_type,
                'expires_at' => $active?->expires_at?->toIso8601String(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function canViewProfile(User $viewer, User $profileUser): bool
    {
        if ($profileUser->id === $viewer->id) {
            return true;
        }

        if (Block::where('blocker_id', $viewer->id)->where('blocked_id', $profileUser->id)->exists()) {
            return true;
        }

        return $this->conversations->isVisiblePartner($viewer, $profileUser);
    }

    /**
     * @return list<int>
     */
    private function parsePostIds(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $raw))));
    }
}

