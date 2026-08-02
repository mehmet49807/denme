<?php

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class StoryGroupService
{
    /** Paketsiz kullanıcılar da mümkün olduğunca tüm hikayeleri görsün. */
    private const DISCOVERY_STORY_LIMIT = 500;

    private const DISCOVERY_GROUP_LIMIT = 150;

    public function __construct(
        private GenderFilterService $genderFilter,
        private StoryService $stories,
        private CountryMetaService $countries,
    ) {}

    public function loadUserStoryGroup(User $user, ?User $viewer = null): ?array
    {
        $stories = Story::active()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        if ($viewer) {
            $stories = $stories->filter(fn (Story $story) => $story->visibleTo($viewer))->values();
        }

        if ($stories->isEmpty()) {
            return null;
        }

        return $this->formatStoryGroup($user, $stories);
    }

    public function loadOwnStoryGroup(User $viewer): ?array
    {
        $ownStories = Story::active()
            ->where('user_id', $viewer->id)
            ->latest()
            ->get();

        if ($ownStories->isEmpty()) {
            return null;
        }

        $group = $this->formatStoryGroup($viewer, $ownStories);
        $group['is_own'] = true;

        return $group;
    }

    public function loadDiscoveryGroups(User $viewer, ?Collection $visibleUserIds = null): Collection
    {
        $memberStories = $this->loadMemberDiscoveryStories($viewer, $visibleUserIds);
        $officialStories = $this->loadOfficialDiscoveryStories($viewer);

        $stories = $officialStories
            ->concat($memberStories)
            ->unique('id')
            ->values();

        return $stories
            ->filter(fn ($story) => $story->user)
            ->groupBy('user_id')
            ->take(self::DISCOVERY_GROUP_LIMIT)
            ->map(function ($userStories) {
                $user = $userStories->first()->user;

                return $this->formatStoryGroup($user, $userStories);
            })
            ->values();
    }

    /**
     * @return Collection<int, Story>
     */
    private function loadMemberDiscoveryStories(User $viewer, ?Collection $visibleUserIds = null): Collection
    {
        $visibleSubquery = $visibleUserIds !== null
            ? null
            : $this->genderFilter->visibleUsersQuery($viewer);

        if ($visibleUserIds !== null && $visibleUserIds->isEmpty()) {
            return collect();
        }

        $now = now()->toDateTimeString();
        $premiumWith = ['user.premiumSubscriptions' => function ($q) {
            $q->active()->latest('expires_at');
        }];

        try {
            // Önce paket / boost ile sırala — görüntüleme herkese açık, sıralama sadece öne çıkarma.
            $query = Story::active()
                ->with($premiumWith)
                ->join('users', 'users.id', '=', 'stories.user_id')
                ->where('stories.user_id', '!=', $viewer->id)
                ->where($this->memberAudienceConstraint())
                ->orderByRaw('CASE WHEN users.boost_until IS NOT NULL AND users.boost_until > ? THEN 0 ELSE 1 END', [$now])
                ->orderByRaw(User::packageTypeOrderSql('users.id'), [$now])
                ->orderByDesc('stories.created_at')
                ->select('stories.*')
                ->limit(self::DISCOVERY_STORY_LIMIT);

            if ($visibleUserIds !== null) {
                $query->whereIn('stories.user_id', $visibleUserIds);
            } else {
                $query->whereIn('stories.user_id', (clone $visibleSubquery)->select('users.id'));
            }

            return $query->get();
        } catch (\Throwable) {
            // Sıralama sorgusu düşerse paketsiz kullanıcılar yine tüm hikayeleri görsün.
            $query = Story::active()
                ->with($premiumWith)
                ->where('user_id', '!=', $viewer->id)
                ->where($this->memberAudienceConstraint())
                ->latest()
                ->limit(self::DISCOVERY_STORY_LIMIT);

            if ($visibleUserIds !== null) {
                $query->whereIn('user_id', $visibleUserIds);
            } else {
                $query->whereIn('user_id', (clone $visibleSubquery)->select('users.id'));
            }

            return $query->get();
        }
    }

    /**
     * Yönetici hikâyeleri: tüm üyeler / kadınlar / erkekler hedefiyle — cinsiyet filtresinden bağımsız.
     *
     * @return Collection<int, Story>
     */
    private function loadOfficialDiscoveryStories(User $viewer): Collection
    {
        if (! $this->hasAudienceColumn()) {
            return collect();
        }

        $gender = in_array($viewer->gender, ['male', 'female'], true) ? $viewer->gender : null;

        return Story::active()
            ->with('user')
            ->where('user_id', '!=', $viewer->id)
            ->whereIn('audience', array_values(array_filter([
                Story::AUDIENCE_ALL,
                $gender,
            ])))
            ->latest()
            ->limit(48)
            ->get();
    }

    /**
     * Üye hikâyeleri audience=null (veya kolon yok) — resmi hedefli hikâyeler ayrı yüklenir.
     */
    private function memberAudienceConstraint(): \Closure
    {
        return function ($query) {
            if (! $this->hasAudienceColumn()) {
                return;
            }

            $query->where(function ($q) {
                $q->whereNull('stories.audience')
                    ->orWhere('stories.audience', '');
            });
        };
    }

    private function hasAudienceColumn(): bool
    {
        static $has = null;

        if ($has !== null) {
            return $has;
        }

        try {
            Story::ensureAudienceColumn();
            $has = Schema::hasColumn('stories', 'audience');
        } catch (\Throwable) {
            $has = false;
        }

        return $has;
    }

    public function formatStoryGroup(User $user, $stories): array
    {
        $storyItems = collect($stories)->values();
        $isOfficial = $storyItems->contains(fn ($story) => method_exists($story, 'isOfficial') && $story->isOfficial());

        $country = trim((string) ($user->country ?: 'Türkiye'));
        $city = trim((string) ($user->city ?: ''));
        $iso = $this->countries->isoForCountry($country !== '' ? $country : 'Türkiye');
        $flagUrl = $this->countries->flagUrl($iso !== '' ? $iso : 'tr');

        $showPremium = ! $isOfficial
            && method_exists($user, 'showsPremiumMemberBadge')
            && $user->showsPremiumMemberBadge();
        $badge = $showPremium && method_exists($user, 'packageBadge') ? $user->packageBadge() : null;
        $premiumSticker = null;
        if ($showPremium) {
            $premiumSticker = [
                'label' => is_array($badge) ? (string) ($badge['badge_label'] ?? 'Premium') : 'Premium',
                'type' => is_array($badge) ? (string) ($badge['type'] ?? 'premium') : 'premium',
                'icon' => is_array($badge) ? (string) ($badge['badge_icon'] ?? 'crown') : 'crown',
                'from' => is_array($badge) ? (string) ($badge['gradient_from'] ?? '#7c3aed') : '#7c3aed',
                'to' => is_array($badge) ? (string) ($badge['gradient_to'] ?? '#db2777') : '#db2777',
            ];
        }

        $isFeatured = ! $isOfficial && (
            (method_exists($user, 'packageRank') && $user->packageRank() >= 2)
            || (method_exists($user, 'isBoosted') && $user->isBoosted())
        );

        return [
            'user_id' => $user->id,
            'username' => $isOfficial ? 'Gönül Köprüsü' : $user->username,
            'profile_url' => $isOfficial
                ? rtrim((string) config('app.site_url', config('app.url')), '/')
                : rtrim((string) config('app.site_url'), '/').'/users/'.$user->username,
            'profile_photo_url' => $user->profile_photo_url,
            'is_online' => method_exists($user, 'isOnline') ? $user->isOnline() : false,
            'is_own' => false,
            'is_official' => $isOfficial,
            'country' => $isOfficial ? 'Türkiye' : $country,
            'city' => $isOfficial ? '' : $city,
            'flag_url' => $flagUrl,
            'package_type' => method_exists($user, 'activePackageType') ? $user->activePackageType() : null,
            'show_premium_sticker' => $showPremium,
            'premium_sticker' => $premiumSticker,
            'is_featured' => $isFeatured,
            'items' => $storyItems->map(fn ($story) => [
                'id' => $story->id,
                'media_url' => $story->media_url,
                'media_type' => $story->is_video ? 'video' : 'image',
                'audience' => $story->audience ?? null,
            ])->values()->all(),
        ];
    }
}
