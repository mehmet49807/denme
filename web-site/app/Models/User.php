<?php

namespace App\Models;

use App\Support\HobbyCatalog;
use App\Support\LocaleManager;
use App\Support\RelationshipStatus;
use App\Services\UserMailService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const MALE_TRIAL_DAYS = 3;
    public const ONLINE_MINUTES = 5;
    public const REFERRAL_REWARD_DAYS = 3;

    protected $fillable = [
        'username', 'first_name', 'last_name', 'email', 'password',
        'phone', 'gender', 'country', 'city', 'district', 'profile_photo_url', 'hobbies', 'google_id',
        'bio', 'relationship_status', 'relationship_expectation', 'birth_date', 'gallery_photos',
        'is_verified', 'visibility', 'quiet_hours_enabled', 'quiet_hours_start', 'quiet_hours_end',
        'read_receipts_enabled', 'theme_preference', 'boost_until', 'last_boost_at', 'fake_score',
        'role', 'is_banned', 'banned_at', 'banned_reason', 'trial_ends_at', 'locale',
        'referral_code', 'referred_by_user_id', 'utm_source', 'utm_medium', 'utm_campaign',
        'registration_source', 'last_lifecycle_email_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'banned_at' => 'datetime',
            'last_active_at' => 'datetime',
            'last_lifecycle_email_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'birth_date' => 'date',
            'boost_until' => 'datetime',
            'last_boost_at' => 'datetime',
            'is_banned' => 'boolean',
            'is_verified' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
            'read_receipts_enabled' => 'boolean',
            'password' => 'hashed',
            'hobbies' => 'array',
            'gallery_photos' => 'array',
            'fake_score' => 'integer',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function premiumSubscriptions()
    {
        return $this->hasMany(PremiumSubscription::class);
    }

    public function profileViewsReceived()
    {
        return $this->hasMany(ProfileView::class, 'viewed_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'moderator', 'support'], true);
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator'], true);
    }

    public function isOnTrial(): bool
    {
        return $this->gender === 'male'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function isPremium(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->gender === 'female') {
            return false;
        }

        if ($this->relationLoaded('premiumSubscriptions')) {
            return $this->premiumSubscriptions
                ->contains(fn ($sub) => (bool) $sub->is_active && $sub->expires_at && $sub->expires_at->isFuture());
        }

        return $this->premiumSubscriptions()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public const PACKAGE_RANKS = [
        'pro' => 1,
        'gold' => 2,
        'platinum' => 3,
    ];

    public function activePackageType(): ?string
    {
        try {
            if ($this->gender !== 'male' || $this->isAdmin() || ! $this->isPremium()) {
                return null;
            }

            if ($this->relationLoaded('premiumSubscriptions')) {
                $subscription = $this->premiumSubscriptions
                    ->filter(fn ($sub) => (bool) $sub->is_active && $sub->expires_at && $sub->expires_at->isFuture())
                    ->sortByDesc(fn ($sub) => $sub->expires_at?->getTimestamp() ?? 0)
                    ->first();
            } else {
                $subscription = $this->premiumSubscriptions()
                    ->active()
                    ->latest('expires_at')
                    ->first();
            }

            $type = $subscription?->package_type;

            return is_string($type) && $type !== '' ? $type : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function packageRank(): int
    {
        if ($this->isAdmin() || $this->gender === 'female') {
            return 99;
        }

        $type = $this->activePackageType();

        return self::PACKAGE_RANKS[$type] ?? 0;
    }

    public function hasPackageAtLeast(string $minType): bool
    {
        if ($this->isAdmin() || $this->gender === 'female') {
            return true;
        }

        $need = self::PACKAGE_RANKS[$minType] ?? 99;

        return $this->packageRank() >= $need;
    }

    public function packageBadge(): ?array
    {
        try {
            if (! class_exists(\App\Services\PremiumPackagesService::class)) {
                return null;
            }

            return app(\App\Services\PremiumPackagesService::class)->badgeForUser($this);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Hikaye görüntüleme: paket gerekmez.
     * Pro/Gold/Platinum olmayan kullanıcılar da tüm hikayeleri görebilir.
     */
    public function canViewStories(): bool
    {
        return ! $this->is_banned;
    }

    /** Gold+ veya deneme: hikaye paylaşımı (görüntüleme serbest) */
    public function canPostStories(): bool
    {
        if ($this->isAdmin() || $this->gender === 'female') {
            return true;
        }

        if ($this->isOnTrial()) {
            return true;
        }

        return $this->hasPackageAtLeast('gold');
    }

    /** Tüm paketler + deneme: sınırsız mesajlaşma */
    public function canSendMessages(): bool
    {
        return $this->canUseMalePremiumFeatures();
    }

    /**
     * Galeri erişimi: kadınlar, adminler ve Pro+ erkekler.
     * (Geriye dönük uyumluluk — kimler baktı ayrı metoda taşındı.)
     */
    public function canAccessPremiumProfileFeatures(): bool
    {
        return $this->canManageProfileGallery();
    }

    /** Platinum: Kimler baktı */
    public function canAccessWhoViewed(): bool
    {
        if ($this->isAdmin() || $this->gender === 'female') {
            return true;
        }

        return $this->hasPackageAtLeast('platinum');
    }

    /**
     * Başkasının galeri fotoğraflarını görme:
     * kadınlar ve adminler her zaman, erkeklerde Pro+.
     */
    public function canViewProfileGallery(): bool
    {
        if ($this->isAdmin() || $this->gender === 'female') {
            return true;
        }

        return $this->gender === 'male' && $this->isPremium();
    }

    /** Kendi galerisine fotoğraf ekleme / silme — Pro+ */
    public function canManageProfileGallery(): bool
    {
        return $this->canViewProfileGallery();
    }

    /** Gold+: profil öne çıkarma (boost) */
    public function canUseProfileBoost(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->gender !== 'male') {
            return false;
        }

        return $this->hasPackageAtLeast('gold');
    }

    /**
     * Aktif paket tipine göre SQL sıra ifadesi (0 = en üstte).
     * Bind için bir adet datetime parametresi bekler.
     *
     * Prefer applyPackageRankingJoin() on hot paths; this remains for
     * callers that cannot join.
     */
    public static function packageTypeOrderSql(string $userIdExpression = 'users.id'): string
    {
        return "CASE COALESCE((
            SELECT package_type FROM premium_subscriptions
            WHERE premium_subscriptions.user_id = {$userIdExpression}
              AND premium_subscriptions.is_active = 1
              AND premium_subscriptions.expires_at > ?
            ORDER BY premium_subscriptions.expires_at DESC
            LIMIT 1
        ), '')
            WHEN 'platinum' THEN 0
            WHEN 'gold' THEN 1
            WHEN 'pro' THEN 2
            ELSE 3
        END";
    }

    /**
     * Left-join the latest active package once, then order by it (cheaper than correlated subquery).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User|\App\Models\Post>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User|\App\Models\Post>
     */
    public static function applyPackageRankingJoin($query, string $userIdColumn = 'users.id')
    {
        $now = now()->toDateTimeString();
        $sub = \Illuminate\Support\Facades\DB::table('premium_subscriptions as ps1')
            ->select('ps1.user_id', 'ps1.package_type')
            ->where('ps1.is_active', 1)
            ->where('ps1.expires_at', '>', $now)
            ->whereRaw('ps1.id = (
                SELECT ps2.id FROM premium_subscriptions ps2
                WHERE ps2.user_id = ps1.user_id
                  AND ps2.is_active = 1
                  AND ps2.expires_at > ?
                ORDER BY ps2.expires_at DESC
                LIMIT 1
            )', [$now]);

        return $query
            ->leftJoinSub($sub, 'active_pkg', function ($join) use ($userIdColumn) {
                $join->on('active_pkg.user_id', '=', $userIdColumn);
            })
            ->orderByRaw("CASE COALESCE(active_pkg.package_type, '')
                WHEN 'platinum' THEN 0
                WHEN 'gold' THEN 1
                WHEN 'pro' THEN 2
                ELSE 3
            END");
    }

    /**
     * Keşif / üye listelerinde paket + boost önceliği.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public static function applyDiscoveryRanking($query)
    {
        $now = now()->toDateTimeString();

        if (empty($query->getQuery()->columns)) {
            $query->select('users.*');
        }

        $query = $query
            ->orderByRaw('CASE WHEN users.boost_until IS NOT NULL AND users.boost_until > ? THEN 0 ELSE 1 END', [$now]);

        try {
            return static::applyPackageRankingJoin($query, 'users.id')
                ->latest('users.last_active_at');
        } catch (\Throwable) {
            return $query
                ->orderByRaw(self::packageTypeOrderSql('users.id'), [$now])
                ->latest('users.last_active_at');
        }
    }

    /**
     * Önerilen üyeler: boost → Platinum → Gold → Pro sırası.
     *
     * @param  \Illuminate\Support\Collection<int, int>|\Illuminate\Database\Eloquent\Builder<\App\Models\User>|array<int, int>  $visible
     * @return \Illuminate\Support\Collection<int, User>
     */
    public static function recommendedMembers($visible, int $excludeUserId, int $limit = 12)
    {
        $query = static::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('id', '!=', $excludeUserId)
            ->where(function ($q) {
                $q->where('boost_until', '>', now())
                    ->orWhereHas('premiumSubscriptions', function ($sub) {
                        $sub->where('is_active', true)
                            ->where('expires_at', '>', now())
                            ->whereIn('package_type', ['pro', 'gold', 'platinum']);
                    });
            });

        static::constrainToVisible($query, $visible);

        return static::applyDiscoveryRanking($query)
            ->with(['premiumSubscriptions' => fn ($q) => $q->active()->latest('expires_at')])
            ->limit($limit)
            ->get();
    }

    /**
     * Erkek akışı: karşı cins (kadın) önerileri.
     * Paket şartı yok — fotoğraflı / öne çıkan / aktif üyeler.
     *
     * @param  \Illuminate\Support\Collection<int, int>|\Illuminate\Database\Eloquent\Builder<\App\Models\User>|array<int, int>  $visible
     * @return \Illuminate\Support\Collection<int, User>
     */
    public static function recommendedForMaleFeed($visible, int $excludeUserId, int $limit = 12)
    {
        $now = now()->toDateTimeString();

        $query = static::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('gender', 'female')
            ->where('id', '!=', $excludeUserId);

        static::constrainToVisible($query, $visible);

        return $query
            ->with(['premiumSubscriptions' => fn ($q) => $q->active()->latest('expires_at')])
            ->orderByRaw('CASE WHEN boost_until IS NOT NULL AND boost_until > ? THEN 0 ELSE 1 END', [$now])
            ->orderByRaw('CASE WHEN profile_photo_url IS NOT NULL AND profile_photo_url != "" THEN 0 ELSE 1 END')
            ->latest('last_active_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @param  \Illuminate\Support\Collection<int, int>|\Illuminate\Database\Eloquent\Builder<\App\Models\User>|array<int, int>  $visible
     */
    private static function constrainToVisible($query, $visible): void
    {
        if ($visible instanceof \Illuminate\Database\Eloquent\Builder) {
            $query->whereIn('id', (clone $visible)->select('users.id'));

            return;
        }

        $ids = collect($visible)->filter()->values();
        if ($ids->isEmpty()) {
            $query->whereRaw('0 = 1');

            return;
        }

        $query->whereIn('id', $ids);
    }

    /**
     * Gönderi akışında paket + boost önceliği (Platinum / Gold öne çıkar).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Post>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Post>
     */
    public static function applyContentRanking($query, string $postsTable = 'posts')
    {
        $now = now()->toDateTimeString();

        $query = $query
            ->join('users', 'users.id', '=', $postsTable.'.user_id')
            ->select($postsTable.'.*')
            ->orderByRaw('CASE WHEN users.boost_until IS NOT NULL AND users.boost_until > ? THEN 0 ELSE 1 END', [$now]);

        try {
            return static::applyPackageRankingJoin($query, 'users.id')
                ->orderByDesc($postsTable.'.created_at');
        } catch (\Throwable) {
            return $query
                ->orderByRaw(self::packageTypeOrderSql('users.id'), [$now])
                ->orderByDesc($postsTable.'.created_at');
        }
    }

    /** Koleksiyon sıralaması için görünürlük skoru (yüksek = önde). */
    public function contentVisibilityScore(): int
    {
        $score = $this->packageRank() * 10;
        if ($this->isBoosted()) {
            $score += 100;
        }

        return $score;
    }

    private function canUseMalePremiumFeatures(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->gender === 'female') {
            return true;
        }

        return $this->isOnTrial() || $this->isPremium();
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInSeconds($this->trial_ends_at) / 86400));
    }

    public function trialHoursRemaining(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInSeconds($this->trial_ends_at) / 3600));
    }

    public static function trialEndsAtForNewMale(): \Illuminate\Support\Carbon
    {
        return now()->addDays(self::MALE_TRIAL_DAYS);
    }

    public function oppositeGender(): string
    {
        return $this->gender === 'male' ? 'female' : 'male';
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function displayNameFor(?User $viewer = null): string
    {
        if ($viewer?->isAdmin()) {
            return $this->fullName() ?: $this->username;
        }

        return $this->username;
    }

    /** Onaylı / doğrulanmış üyelerde tik (herkese değil) */
    public function showsPremiumVerifiedTick(): bool
    {
        if ($this->is_banned) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return (bool) $this->is_verified;
    }

    public function showsPremiumMemberBadge(): bool
    {
        try {
            if ($this->gender !== 'male' || ! $this->isPremium()) {
                return false;
            }

            $badge = $this->packageBadge();

            return $badge !== null || $this->activePackageType() === null;
        } catch (\Throwable) {
            return $this->gender === 'male' && $this->isPremium();
        }
    }

    /** Güven rozeti — doğrulanmış üye */
    public function showsTrustBadge(): bool
    {
        return (bool) $this->is_verified;
    }

    /** Kadınlara ücretsiz güvenlik vurgusu rozeti */
    public function showsSafetyBadge(): bool
    {
        return $this->gender === 'female';
    }

    /** En az bir başarılı daveti olan üye rozeti */
    public function showsReferralBadge(): bool
    {
        try {
            return \App\Models\Referral::query()
                ->where('referrer_id', $this->id)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function showsTrialBadge(): bool
    {
        return $this->gender === 'male'
            && $this->isOnTrial()
            && ! $this->isPremium();
    }

    public function isBoosted(): bool
    {
        return $this->boost_until !== null && $this->boost_until->isFuture();
    }

    public function canBoostToday(): bool
    {
        if ($this->last_boost_at === null) {
            return true;
        }

        return $this->last_boost_at->lt(now()->startOfDay());
    }

    public function activateDailyBoost(int $hours = 12): void
    {
        $this->update([
            'boost_until' => now()->addHours($hours),
            'last_boost_at' => now(),
        ]);
    }

    /** @return list<string> */
    public function galleryPhotos(): array
    {
        $photos = $this->gallery_photos ?? [];

        return array_values(array_filter(is_array($photos) ? $photos : []));
    }

    public function wantsReadReceipts(): bool
    {
        return $this->read_receipts_enabled !== false;
    }

    public function isInQuietHours(): bool
    {
        if (! $this->quiet_hours_enabled) {
            return false;
        }

        $start = (string) ($this->quiet_hours_start ?: '23:00');
        $end = (string) ($this->quiet_hours_end ?: '07:00');
        $now = now()->format('H:i');

        if ($start <= $end) {
            return $now >= $start && $now < $end;
        }

        return $now >= $start || $now < $end;
    }

    public function isVisibleTo(?User $viewer): bool
    {
        if (! $viewer || $viewer->id === $this->id || $viewer->isAdmin()) {
            return true;
        }

        $visibility = $this->visibility ?: 'everyone';

        return match ($visibility) {
            'nobody' => false,
            'premium' => $viewer->isPremium() || $viewer->gender === 'female',
            'matches' => $viewer->gender === $this->oppositeGender(),
            default => true,
        };
    }

    public function age(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    /** @return array{id: string, label: string, icon: string}|null */
    public function resolvedRelationshipStatus(): ?array
    {
        return RelationshipStatus::resolve($this->relationship_status);
    }

    public function isOnline(): bool
    {
        if ($this->last_active_at === null) {
            return false;
        }

        return $this->last_active_at->greaterThan(now()->subMinutes(self::ONLINE_MINUTES));
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'profile_photo_url' => $this->profile_photo_url,
            'country' => $this->country ?? 'Türkiye',
            'city' => $this->city,
            'district' => $this->district,
            'bio' => $this->bio,
            'relationship_status' => $this->relationship_status,
            'relationship_status_label' => RelationshipStatus::label($this->relationship_status),
            'relationship_expectation' => $this->relationship_expectation,
            'gallery_photos' => $this->galleryPhotos(),
            'hobbies' => $this->resolvedHobbies(),
            'gender' => $this->gender,
            'age' => $this->age(),
            'show_premium_tick' => $this->showsPremiumVerifiedTick(),
            'show_premium_badge' => $this->showsPremiumMemberBadge(),
            'show_trust_badge' => $this->showsTrustBadge(),
            'show_safety_badge' => $this->showsSafetyBadge(),
            'show_trial_badge' => $this->showsTrialBadge(),
            'is_boosted' => $this->isBoosted(),
            'is_online' => $this->isOnline(),
            'trial_days_remaining' => $this->trialDaysRemaining(),
        ];
    }

    /** @return list<array{id: string, label: string, icon: string, color: string}> */
    public function resolvedHobbies(): array
    {
        return HobbyCatalog::resolve(HobbyCatalog::normalize($this->hobbies));
    }

    public function toOwnerArray(): array
    {
        return array_merge($this->toPublicArray(), [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'hobby_ids' => $this->hobbies ?? [],
            'is_premium' => $this->isPremium(),
            'is_on_trial' => $this->isOnTrial(),
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'trial_days_remaining' => $this->trialDaysRemaining(),
            'can_view_stories' => $this->canViewStories(),
            'can_post_stories' => $this->canPostStories(),
            'can_send_messages' => $this->canSendMessages(),
            'can_access_who_viewed' => $this->canAccessWhoViewed(),
            'can_manage_gallery' => $this->canManageProfileGallery(),
            'package_type' => $this->activePackageType(),
            'package_rank' => $this->packageRank(),
            'locale' => $this->locale ?: LocaleManager::default(),
            'visibility' => $this->visibility ?: 'everyone',
            'read_receipts_enabled' => $this->wantsReadReceipts(),
            'theme_preference' => $this->theme_preference ?: 'light',
            'quiet_hours_enabled' => (bool) $this->quiet_hours_enabled,
        ]);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        app(UserMailService::class)->sendPasswordReset($this, $token);
    }
}
