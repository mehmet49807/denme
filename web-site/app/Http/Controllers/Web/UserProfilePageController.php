<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Like;
use App\Models\Post;
use App\Models\ProfileView;
use App\Models\Report;
use App\Models\User;
use App\Services\GenderFilterService;
use App\Services\StoryGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserProfilePageController extends Controller
{
    public function __construct(
        private GenderFilterService $genderFilter,
        private StoryGroupService $storyGroups,
    ) {}

    public function index(Request $request): View
    {
        $viewer = $request->user();

        $filter = strtolower((string) $request->query('filter', 'all'));
        if (! in_array($filter, ['all', 'online', 'city'], true)) {
            $filter = 'all';
        }

        $users = User::where('role', 'user')
            ->where('is_banned', false)
            ->where('id', '!=', $viewer->id)
            ->where(function ($q) use ($viewer) {
                $this->genderFilter->applyDiscoveryFilters($q, $viewer);
            })
            ->with(['premiumSubscriptions' => fn ($q) => $q->active()->latest('expires_at')])
            ->withCount(['posts' => fn ($q) => $q->where('is_active', true)]);

        if ($filter === 'online') {
            $users->whereNotNull('last_active_at')
                ->where('last_active_at', '>=', now()->subMinutes(User::ONLINE_MINUTES));
        } elseif ($filter === 'city' && filled($viewer->city)) {
            $users->where('country', $viewer->country ?: 'Türkiye')
                ->where('city', $viewer->city);
        }

        $users = User::applyDiscoveryRanking($users)->paginate(24)->withQueryString();

        return view('web.users', compact('users', 'viewer', 'filter'));
    }

    public function show(Request $request, string $username): View|RedirectResponse
    {
        $viewer = $request->user();
        $user = User::where('username', $username)->where('role', 'user')->firstOrFail();

        if ($user->id === $viewer->id) {
            return redirect()->route('profile');
        }

        $viewerHasBlocked = Block::where('blocker_id', $viewer->id)
            ->where('blocked_id', $user->id)
            ->exists();

        if (! $viewerHasBlocked) {
            $visible = User::where('id', $user->id)
                ->where(function ($q) use ($viewer) {
                    $this->genderFilter->applyDiscoveryFilters($q, $viewer);
                })
                ->exists();

            if (! $visible || ! $user->isVisibleTo($viewer)) {
                abort(404);
            }
        }

        ProfileView::record($viewer, $user);

        $posts = Post::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->limit(36)
            ->get();

        $targetStoryGroup = $this->storyGroups->loadUserStoryGroup($user);

        $likedPostIds = Like::where('user_id', $viewer->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('post_id')
            ->all();

        return view('web.user-profile', compact('user', 'posts', 'viewer', 'targetStoryGroup', 'likedPostIds', 'viewerHasBlocked'));
    }

    public function block(Request $request, string $username): RedirectResponse
    {
        $viewer = $request->user();
        $user = User::where('username', $username)->where('role', 'user')->firstOrFail();

        if ($user->id === $viewer->id) {
            abort(403);
        }

        Block::firstOrCreate([
            'blocker_id' => $viewer->id,
            'blocked_id' => $user->id,
        ]);

        $this->genderFilter->forgetVisibleUserIds($viewer->id);
        $this->genderFilter->forgetVisibleUserIds($user->id);

        return redirect()
            ->route('users.show', $user->username)
            ->with('success', __('app.messages.blocked', ['name' => $user->username]));
    }

    public function unblock(Request $request, string $username): RedirectResponse
    {
        $viewer = $request->user();
        $user = User::where('username', $username)->where('role', 'user')->firstOrFail();

        if ($user->id === $viewer->id) {
            abort(403);
        }

        $deleted = Block::where('blocker_id', $viewer->id)
            ->where('blocked_id', $user->id)
            ->delete();

        if ($deleted) {
            $this->genderFilter->forgetVisibleUserIds($viewer->id);
            $this->genderFilter->forgetVisibleUserIds($user->id);
        }

        return redirect()
            ->route('users.show', $user->username)
            ->with('success', __('app.profile.unblocked', ['name' => $user->username]));
    }

    public function report(Request $request, string $username): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Şikayet sebebini yazın.',
        ]);

        $viewer = $request->user();
        $user = User::where('username', $username)->where('role', 'user')->firstOrFail();

        if ($user->id === $viewer->id) {
            abort(403);
        }

        $viewerHasBlocked = Block::where('blocker_id', $viewer->id)
            ->where('blocked_id', $user->id)
            ->exists();

        if (! $viewerHasBlocked) {
            $visible = User::where('id', $user->id)
                ->where(function ($q) use ($viewer) {
                    $this->genderFilter->applyDiscoveryFilters($q, $viewer);
                })
                ->exists();

            if (! $visible) {
                abort(404);
            }
        }

        Report::create([
            'reporter_id' => $viewer->id,
            'reported_id' => $user->id,
            'reason' => $request->reason,
        ]);

        return redirect()
            ->route('users.show', $user->username)
            ->with('success', 'Şikayetiniz alındı. Moderasyon ekibimiz inceleyecek.');
    }
}
