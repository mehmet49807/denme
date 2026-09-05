<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\ProfileLike;
use App\Models\User;
use App\Services\DiscoveryFilterService;
use App\Services\GenderFilterService;
use App\Support\HobbyCatalog;
use App\Support\RelationshipStatus;
use App\Support\SeoHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private GenderFilterService $genderFilter,
        private DiscoveryFilterService $discoveryFilters,
    ) {}

    public function index(Request $request): View
    {
        SeoHelper::set('title', 'Üye Ara — Gönül Köprüsü');
        SeoHelper::set('description', 'Yaş, şehir, hobi ve daha fazlasıyla Gönül Köprüsü üyelerini keşfedin.');
        SeoHelper::set('robots', 'noindex,follow');

        $filters = $this->discoveryFilters->parse($request);
        $users = null;
        $emptyMessage = 'Aramaya başlamak için filtreleri kullanın veya en az 2 karakter yazın.';

        $hasSearch = $filters['active'] || mb_strlen($filters['q']) >= 2;

        if ($hasSearch) {
            if ($filters['q'] !== '' && mb_strlen($filters['q']) < 2 && ! $filters['active']) {
                $emptyMessage = 'Arama için en az 2 karakter girin.';
            } else {
                $query = $this->baseQuery($request);
                $users = $this->discoveryFilters->apply($query, $filters)
                    ->paginate(24)
                    ->withQueryString();

                if ($users->total() === 0) {
                    $emptyMessage = 'Bu filtrelere uygun üye bulunamadı. Filtreleri gevşetmeyi deneyin.';
                }
            }
        }

        $likedUserIds = [];
        $followingUserIds = [];
        $viewer = $request->user();
        if ($viewer && $users) {
            $pageUserIds = $users->getCollection()->pluck('id');
            if (class_exists(ProfileLike::class)) {
                ProfileLike::ensureTable();
                $likedUserIds = ProfileLike::query()
                    ->where('liker_id', $viewer->id)
                    ->whereIn('liked_id', $pageUserIds)
                    ->pluck('liked_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
            if (class_exists(Follow::class)) {
                Follow::ensureTable();
                $followingUserIds = Follow::query()
                    ->where('follower_id', $viewer->id)
                    ->whereIn('following_id', $pageUserIds)
                    ->pluck('following_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        }

        return view('web.search', [
            'q' => $filters['q'],
            'filters' => $filters,
            'users' => $users,
            'likedUserIds' => $likedUserIds,
            'followingUserIds' => $followingUserIds,
            'emptyMessage' => $emptyMessage,
            'suggestUrl' => route('search.suggest'),
            'hobbyOptions' => HobbyCatalog::all(),
            'relationshipOptions' => RelationshipStatus::all(),
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) > 80) {
            $q = mb_substr($q, 0, 80);
        }

        if (mb_strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $viewer = $request->user();
        $filters = $this->discoveryFilters->parse($request);
        $filters['q'] = $q;
        $users = $this->discoveryFilters->apply($this->baseQuery($request), $filters)->limit(8)->get();

        $data = $users->map(function (User $user) use ($viewer) {
            return [
                'username' => $user->username,
                'city' => $user->city,
                'profile_photo_url' => $user->profile_photo_url,
                'url' => $viewer
                    ? route('users.show', $user->username)
                    : route('register'),
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    private function baseQuery(Request $request)
    {
        $viewer = $request->user();

        $query = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->with(['premiumSubscriptions' => fn ($q) => $q->active()->latest('expires_at')])
            ->withCount(['posts' => fn ($q) => $q->where('is_active', true)]);

        if ($viewer) {
            $query->where('id', '!=', $viewer->id)
                ->where(function ($builder) use ($viewer) {
                    $this->genderFilter->applyDiscoveryFilters($builder, $viewer);
                });

            return User::applyDiscoveryRanking($query);
        }

        return $query->orderByDesc('last_active_at')->orderByDesc('id');
    }
}
