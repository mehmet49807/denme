<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\ProfileLike;
use App\Models\User;
use App\Services\GenderFilterService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileLikeController extends Controller
{
    public function __construct(
        private GenderFilterService $genderFilter,
        private NotificationService $notifications,
    ) {}

    public function toggle(Request $request, string $username): JsonResponse|RedirectResponse
    {
        ProfileLike::ensureTable();

        $viewer = $request->user();
        $target = User::query()->where('username', $username)->where('role', 'user')->firstOrFail();

        if ($target->id === $viewer->id || $target->is_banned) {
            abort(403);
        }

        $blocked = Block::query()
            ->where(function ($q) use ($viewer, $target) {
                $q->where('blocker_id', $viewer->id)->where('blocked_id', $target->id);
            })
            ->orWhere(function ($q) use ($viewer, $target) {
                $q->where('blocker_id', $target->id)->where('blocked_id', $viewer->id);
            })
            ->exists();

        if ($blocked) {
            abort(403);
        }

        $visible = User::query()
            ->where('id', $target->id)
            ->where(function ($q) use ($viewer) {
                $this->genderFilter->applyDiscoveryFilters($q, $viewer);
            })
            ->exists();

        if (! $visible || ! $target->isVisibleTo($viewer)) {
            abort(404);
        }

        $existing = ProfileLike::query()
            ->where('liker_id', $viewer->id)
            ->where('liked_id', $target->id)
            ->first();

        $liked = false;
        $matched = false;

        if ($existing) {
            $existing->delete();
        } else {
            ProfileLike::query()->create([
                'liker_id' => $viewer->id,
                'liked_id' => $target->id,
                'created_at' => now(),
            ]);
            $liked = true;

            $matched = ProfileLike::query()
                ->where('liker_id', $target->id)
                ->where('liked_id', $viewer->id)
                ->exists();

            if ($matched) {
                $this->notifications->notifyMatch($viewer, $target);
            } else {
                $this->notifications->notifyProfileLiked($viewer, $target);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'liked' => $liked,
                'matched' => $matched,
            ]);
        }

        $message = ! $liked
            ? 'Beğeni geri alındı.'
            : ($matched ? 'Karşılıklı beğeni! Artık sohbet edebilirsiniz.' : 'Profil beğenildi.');

        return back()->with('success', $message);
    }

    public function matches(Request $request): View
    {
        ProfileLike::ensureTable();

        $viewer = $request->user();
        $tab = $request->query('tab') === 'incoming' ? 'incoming' : 'matches';
        // Erkeklerde aktif Premium; kadın/admin serbest. Trial açmaz.
        $canRevealMatches = $viewer->canAccessIncomingLikes();

        $likedIds = ProfileLike::query()
            ->where('liker_id', $viewer->id)
            ->pluck('liked_id');

        $matchedQuery = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->whereIn('id', function ($q) use ($viewer, $likedIds) {
                $q->select('liker_id')
                    ->from('profile_likes')
                    ->where('liked_id', $viewer->id)
                    ->whereIn('liker_id', $likedIds);
            })
            ->where(function ($q) use ($viewer) {
                $this->genderFilter->applyDiscoveryFilters($q, $viewer);
            })
            ->latest('last_active_at');

        $incomingQuery = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->whereIn('id', function ($q) use ($viewer, $likedIds) {
                $q->select('liker_id')
                    ->from('profile_likes')
                    ->where('liked_id', $viewer->id)
                    ->whereNotIn('liker_id', $likedIds);
            })
            ->where(function ($q) use ($viewer) {
                $this->genderFilter->applyDiscoveryFilters($q, $viewer);
            })
            ->latest('last_active_at');

        // Kilitliyken yalnızca sayı; kimlik / fotoğraf sızdırılmaz.
        $matchesCount = (clone $matchedQuery)->count();
        $incomingCount = (clone $incomingQuery)->count();

        $matchedUsers = ($canRevealMatches && $tab === 'matches')
            ? $matchedQuery->paginate(24, ['*'], 'matches_page')->withQueryString()
            : null;

        $incomingUsers = ($canRevealMatches && $tab === 'incoming')
            ? $incomingQuery->paginate(24, ['*'], 'incoming_page')->withQueryString()
            : null;

        return view('web.matches', [
            'matchedUsers' => $matchedUsers,
            'incomingUsers' => $incomingUsers,
            'incomingCount' => $incomingCount,
            'matchesCount' => $matchesCount,
            'viewer' => $viewer,
            'tab' => $tab,
            'canRevealMatches' => $canRevealMatches,
            'canRevealIncoming' => $canRevealMatches,
        ]);
    }
}
