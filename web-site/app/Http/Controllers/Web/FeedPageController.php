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
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedPageController extends Controller
{
    public function __construct(
        private GenderFilterService $genderFilter,
        private StoryGroupService $storyGroups,
        private GrowthOnboardingService $onboarding,
    ) {}

    public function index(Request $request): View
    {
        $viewer = $request->user();
        $visibleUserIds = $this->genderFilter->visibleUserIds($viewer);

        $posts = $visibleUserIds->isEmpty()
            ? Post::with('user')->whereRaw('0 = 1')->paginate(20)
            : User::applyContentRanking(
                Post::with('user')
                    ->where('posts.is_active', true)
                    ->whereIn('posts.user_id', $visibleUserIds)
            )->paginate(20);

        $ownStoryGroup = $this->storyGroups->loadOwnStoryGroup($viewer);
        $storyGroups = $this->storyGroups->loadDiscoveryGroups($viewer, $visibleUserIds);

        $likedPostIds = Like::where('user_id', $viewer->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('post_id')
            ->all();

        // Erkek akışı: gönderi altında kadın öneri kartları. Kadın akışında yok.
        $recommendedUsers = strtolower((string) $viewer->gender) === 'male'
            ? User::recommendedForMaleFeed($visibleUserIds, $viewer->id, 12)
            : collect();

        $onboarding = null;
        if ($this->onboarding->shouldShow($viewer) || session('growth_show_onboarding')) {
            $onboarding = $this->onboarding->progress($viewer);
        }

        $showInviteBanner = false;
        if ($onboarding === null) {
            try {
                $showInviteBanner = ! Referral::query()->where('referrer_id', $viewer->id)->exists();
            } catch (\Throwable) {
                $showInviteBanner = true;
            }
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
        ));
    }
}
