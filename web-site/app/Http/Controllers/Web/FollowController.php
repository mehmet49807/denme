<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Follow;
use App\Models\User;
use App\Services\GenderFilterService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        private GenderFilterService $genderFilter,
        private NotificationService $notifications,
    ) {}

    /** Takip / takibi bırak — tüm üyeler kullanabilir. */
    public function toggle(Request $request, string $username): JsonResponse|RedirectResponse
    {
        Follow::ensureTable();

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

        $existing = Follow::query()
            ->where('follower_id', $viewer->id)
            ->where('following_id', $target->id)
            ->first();

        $following = false;
        $followBack = false;

        if ($existing) {
            $existing->delete();
        } else {
            Follow::query()->create([
                'follower_id' => $viewer->id,
                'following_id' => $target->id,
                'created_at' => now(),
            ]);
            $following = true;

            // Geri takip: karşı taraf zaten bizi takip ediyorsa → ona bildir (yalnızca premium).
            $followBack = Follow::query()
                ->where('follower_id', $target->id)
                ->where('following_id', $viewer->id)
                ->exists();

            if ($followBack) {
                $this->notifications->notifyFollowBack($viewer, $target);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'following' => $following,
                'follow_back' => $followBack && $following,
            ]);
        }

        $message = ! $following
            ? 'Takip bırakıldı.'
            : ($followBack ? 'Karşılıklı takip!' : 'Takip edildi.');

        return back()->with('success', $message);
    }
}

