<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GenderFilterService;
use App\Support\SeoHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private GenderFilterService $genderFilter,
    ) {}

    public function index(Request $request): View
    {
        SeoHelper::set('title', 'Üye Ara — Gönül Köprüsü');
        SeoHelper::set('description', 'Kullanıcı adı, şehir veya ilçe ile Gönül Köprüsü üyelerini arayın.');
        SeoHelper::set('robots', 'noindex,follow');

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) > 80) {
            $q = mb_substr($q, 0, 80);
        }

        $users = null;
        $emptyMessage = 'Aramaya başlamak için yukarıdaki alanı kullanın.';

        if ($q !== '') {
            if (mb_strlen($q) < 2) {
                $emptyMessage = 'Arama için en az 2 karakter girin.';
            } else {
                $users = $this->searchQuery($request, $q)
                    ->paginate(24)
                    ->withQueryString();

                if ($users->total() === 0) {
                    $emptyMessage = '“'.$q.'” için sonuç bulunamadı.';
                }
            }
        }

        return view('web.search', [
            'q' => $q,
            'users' => $users,
            'emptyMessage' => $emptyMessage,
            'suggestUrl' => route('search.suggest'),
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
        $users = $this->searchQuery($request, $q)->limit(8)->get();

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
    private function searchQuery(Request $request, string $q)
    {
        $like = '%'.addcslashes($q, '%_\\').'%';
        $viewer = $request->user();

        $query = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where(function ($builder) use ($like) {
                $builder->where('username', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('district', 'like', $like)
                    ->orWhere('first_name', 'like', $like);
            })
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
