<?php

namespace App\Services;

use App\Models\Block;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GenderFilterService
{
    /** @var array<int, Collection<int, int>> */
    private array $blockedIdsCache = [];

    public function filterOppositeGender(Builder $query, User $viewer): Builder
    {
        if ($viewer->isAdmin()) {
            return $query;
        }

        return $query->where('gender', $viewer->oppositeGender());
    }

    public function blockedUserIds(User $viewer): Collection
    {
        if (isset($this->blockedIdsCache[$viewer->id])) {
            return $this->blockedIdsCache[$viewer->id];
        }

        $blockedIds = Block::where('blocker_id', $viewer->id)->pluck('blocked_id');
        $blockerIds = Block::where('blocked_id', $viewer->id)->pluck('blocker_id');

        return $this->blockedIdsCache[$viewer->id] = $blockedIds
            ->merge($blockerIds)
            ->unique()
            ->values();
    }

    public function excludeBlocked(Builder $query, User $viewer): Builder
    {
        if ($viewer->isAdmin()) {
            return $query;
        }

        $allBlocked = $this->blockedUserIds($viewer);

        if ($allBlocked->isEmpty()) {
            return $query;
        }

        return $query->whereNotIn('id', $allBlocked);
    }

    public function applyDiscoveryFilters(Builder $query, User $viewer): Builder
    {
        return $this->excludeBlocked(
            $this->filterOppositeGender($query, $viewer),
            $viewer
        );
    }

    /**
     * Keşifte görünen kullanıcılar (subquery için — ID listesi materialize etmez).
     *
     * @return Builder<\App\Models\User>
     */
    public function visibleUsersQuery(User $viewer): Builder
    {
        $query = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('id', '!=', $viewer->id);

        if ($viewer->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($viewer) {
            $this->applyDiscoveryFilters($q, $viewer);
        });
    }

    public function visibleUserIds(User $viewer): Collection
    {
        return $this->visibleUsersQuery($viewer)->pluck('id');
    }

    public function forgetVisibleUserIds(?int $userId = null): void
    {
        if ($userId === null) {
            $this->blockedIdsCache = [];

            return;
        }

        unset($this->blockedIdsCache[$userId]);
    }
}
