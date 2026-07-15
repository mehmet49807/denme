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

        return $this->premiumSubscriptions()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function canPostStories(): bool
    {
        return $this->canUseMalePremiumFeatures();
    }

    public function canSendMessages(): bool
    {
        return $this->canUseMalePremiumFeatures();
    }

    /**
     * Kimler baktı görüntüleme + galeri yönetimi:
     * kadınlar her zaman, premium erkekler ve adminler.
     */
    public function canAccessPremiumProfileFeatures(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->gender === 'female') {
            return true;
        }

        return $this->gender === 'male' && $this->isPremium();
    }

    /**
     * Başkasının galeri fotoğraflarını görme:
     * kadınlar ve adminler her zaman, erkeklerde yalnızca premium.
     */
    public function canViewProfileGallery(): bool
    {
        return $this->canAccessPremiumProfileFeatures();
    }

    /** Kendi galerisine fotoğraf ekleme / silme */
    public function canManageProfileGallery(): bool
    {
        return $this->canAccessPremiumProfileFeatures();
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

    public function showsPremiumVerifiedTick(): bool
    {
        return $this->gender === 'male' && $this->isPremium();
    }

    public function showsPremiumMemberBadge(): bool
    {
        return $this->showsPremiumVerifiedTick();
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
            'can_post_stories' => $this->canPostStories(),
            'can_send_messages' => $this->canSendMessages(),
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
