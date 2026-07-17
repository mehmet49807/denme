<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
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
            : Post::with('user')
                ->where('is_active', true)
                ->whereIn('user_id', $visibleUserIds)
                ->latest()
                ->paginate(20);

        $ownStoryGroup = $this->storyGroups->loadOwnStoryGroup($viewer);
        $storyGroups = $this->storyGroups->loadDiscoveryGroups($viewer, $visibleUserIds);

        $likedPostIds = Like::where('user_id', $viewer->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('post_id')
            ->all();

        $onboarding = null;
        if ($this->onboarding->shouldShow($viewer) || session('growth_show_onboarding')) {
            $onboarding = $this->onboarding->progress($viewer);
        }

        return view('web.feed', compact(
            'posts',
            'storyGroups',
            'ownStoryGroup',
            'viewer',
            'likedPostIds',
            'onboarding',
        ));
    }
}
