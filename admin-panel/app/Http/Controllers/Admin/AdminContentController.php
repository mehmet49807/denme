<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Story;
use App\Services\MediaUploadService;
use App\Services\StoryGroupService;
use App\Services\StoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContentController extends Controller
{
    public function __construct(
        private MediaUploadService $mediaUpload,
        private StoryGroupService $storyGroups,
        private StoryService $stories,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->get('tab') === 'stories' ? 'stories' : 'posts';
        $search = trim((string) $request->get('search', ''));

        $stats = [
            'posts' => Post::count(),
            'stories_total' => Story::count(),
            'stories_active' => Story::where('expires_at', '>', now())->count(),
        ];

        if ($tab === 'stories') {
            $this->stories->purgeExpiredIfNeeded();

            $query = Story::with('user')->orderByDesc('created_at');

            if ($search !== '') {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }

            $items = $query->paginate(24)->withQueryString();
        } else {
            $query = Post::with('user')->orderByDesc('created_at');

            if ($search !== '') {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }

            $items = $query->paginate(24)->withQueryString();
        }

        $storyGroups = collect();
        $storyIndexMap = [];

        if ($tab === 'stories' && $items->count() > 0) {
            $storyGroups = $items->getCollection()
                ->groupBy('user_id')
                ->map(function ($userStories) {
                    $user = $userStories->first()?->user;
                    if (!$user) {
                        return null;
                    }

                    return $this->storyGroups->formatStoryGroup($user, $userStories);
                })
                ->filter()
                ->values();

            foreach ($storyGroups as $groupIndex => $group) {
                foreach ($group['items'] as $itemIndex => $item) {
                    $storyIndexMap[$item['id']] = [
                        'group' => $groupIndex,
                        'item' => $itemIndex,
                    ];
                }
            }
        }

        return view('admin.content', [
            'tab' => $tab,
            'search' => $search,
            'stats' => $stats,
            'items' => $items,
            'storyGroups' => $storyGroups,
            'storyIndexMap' => $storyIndexMap,
        ]);
    }

    public function destroyPost(Post $post): RedirectResponse
    {
        $this->mediaUpload->deleteByUrl($post->image_url);
        $post->delete();

        return redirect()
            ->route('admin.content', ['tab' => 'posts', 'search' => request('search'), 'page' => request('page')])
            ->with('success', 'Gönderi ve görseli silindi.');
    }

    public function destroyStory(Story $story): RedirectResponse
    {
        $this->mediaUpload->deleteByUrl($story->media_url);
        $story->delete();

        return redirect()
            ->route('admin.content', ['tab' => 'stories', 'search' => request('search'), 'page' => request('page')])
            ->with('success', 'Hikaye ve medya dosyası silindi.');
    }
}
