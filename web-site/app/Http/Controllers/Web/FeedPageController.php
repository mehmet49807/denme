<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use App\Models\Referral;
use App\Models\User;
use App\Services\GenderFilterService;
use App\Services\GrowthOnboardingService;
use App\Services\StoryGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedPageController extends Controller
{
    public function __construct(
        private GenderFilterService $genderFilter,
        private StoryGroupService $storyGroups,
        private GrowthOnboardingService $onboarding,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $viewer = $request->user();
        $visibleQuery = $this->genderFilter->visibleUsersQuery($viewer);

        $posts = User::applyContentRanking(
            Post::with(['user.premiumSubscriptions' => function ($q) {
                $q->active()->latest('expires_at');
            }])
                ->where('posts.is_active', true)
                ->whereIn('posts.user_id', (clone $visibleQuery)->select('users.id'))
        )->simplePaginate(12)->withQueryString();

        $likedPostIds = Like::where('user_id', $viewer->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('post_id')
            ->all();

        if ($request->boolean('partial') || $request->expectsJson()) {
            $html = view('partials.feed-posts-page', [
                'posts' => $posts,
                'viewer' => $viewer,
                'likedPostIds' => $likedPostIds,
                'recommendedUsers' => collect(),
                'pageOffset' => ($posts->currentPage() - 1) * $posts->perPage(),
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }

        $ownStoryGroup = $this->storyGroups->loadOwnStoryGroup($viewer);
        $storyGroups = $this->storyGroups->loadDiscoveryGroups($viewer);

        // Erkek akışı: gönderi altında kadın öneri kartları. Kadın akışında yok.
        $recommendedUsers = strtolower((string) $viewer->gender) === 'male'
            ? User::recommendedForMaleFeed($visibleQuery, $viewer->id, 12)
            : collect();

        $onboarding = null;
        if ($this->onboarding->shouldShow($viewer) || session('growth_show_onboarding')) {
            $onboarding = $this->onboarding->progress($viewer);
        }

        $showInviteBanner = false;
        $inviteDismissed = $request->cookie('gk_invite_banner_off') === '1'
            || $request->cookie('gk_invite_shared') === '1';
        if ($onboarding === null && ! $inviteDismissed) {
            try {
                $showInviteBanner = ! Referral::query()->where('referrer_id', $viewer->id)->exists();
            } catch (\Throwable) {
                $showInviteBanner = true;
            }
        }

        // Onboarding varken deneme/premium üst şeridini gizle (üst üste binmesin)
        $showFeedPromoBanner = $onboarding === null && ! $showInviteBanner;

        $feedNextPageUrl = $posts->nextPageUrl();
        if ($feedNextPageUrl) {
            $feedNextPageUrl .= (str_contains($feedNextPageUrl, '?') ? '&' : '?').'partial=1';
        }

        return view('web.feed', compact(
            'posts',
            'storyGroups',
            'ownStoryGroup',
            'viewer',
            'likedPostIds',
            'recommendedUsers',
            'onboarding',
            'showInviteBanner',
            'showFeedPromoBanner',
            'feedNextPageUrl',
        ));
    }
}
