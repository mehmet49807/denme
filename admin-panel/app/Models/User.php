<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const MALE_TRIAL_DAYS = 3;
    public const REFERRAL_CODE_LENGTH = 8;

    protected $fillable = [
        'username', 'first_name', 'last_name', 'email', 'password',
        'phone', 'gender', 'country', 'city', 'district', 'profile_photo_url',
        'role', 'is_banned', 'banned_at', 'banned_reason', 'trial_ends_at',
        'referral_code', 'referred_by_user_id', 'utm_source', 'utm_medium', 'utm_campaign',
        'registration_source', 'last_lifecycle_email_at',
        'profile_verified_at', 'profile_verified_by', 'profile_verification_note',
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
            'profile_verified_at' => 'datetime',
            'is_banned' => 'boolean',
            'password' => 'hashed',
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

    public function referredBy()
    {
        return $this->belongsTo(self::class, 'referred_by_user_id');
    }

    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralRecord()
    {
        return $this->hasOne(Referral::class, 'referred_id');
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

        return $this->premiumSubscriptions()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function canPostStories(): bool
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
        if (!$this->isOnTrial()) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInSeconds($this->trial_ends_at) / 86400));
    }

    public static function trialEndsAtForNewMale(): \Illuminate\Support\Carbon
    {
        return now()->addDays(self::MALE_TRIAL_DAYS);
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(self::REFERRAL_CODE_LENGTH));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    public function ensureReferralCode(): string
    {
        if (!empty($this->referral_code)) {
            return $this->referral_code;
        }

        $this->forceFill(['referral_code' => self::generateReferralCode()])->save();

        return (string) $this->referral_code;
    }

    public function referralLink(): ?string
    {
        if ($this->gender !== 'male' || empty($this->referral_code)) {
            return null;
        }

        $baseUrl = rtrim(config('app.frontend_url', config('app.url')), '/');

        return "{$baseUrl}/register?ref={$this->referral_code}";
    }

    public function oppositeGender(): string
    {
        return $this->gender === 'male' ? 'female' : 'male';
    }

    public function isProfileVerified(): bool
    {
        return $this->profile_verified_at !== null;
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
            'is_profile_verified' => $this->isProfileVerified(),
        ];
    }

    public function toOwnerArray(): array
    {
        $invitedUsersCount = $this->gender === 'male'
            ? ($this->relationLoaded('referralsMade')
                ? $this->referralsMade->count()
                : $this->referralsMade()->count())
            : 0;

        return array_merge($this->toPublicArray(), [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'is_premium' => $this->isPremium(),
            'is_on_trial' => $this->isOnTrial(),
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'trial_days_remaining' => $this->trialDaysRemaining(),
            'can_post_stories' => $this->canPostStories(),
            'referral_enabled' => $this->gender === 'male',
            'referral_code' => $this->referral_code,
            'referral_link' => $this->referralLink(),
            'invited_users_count' => $invitedUsersCount,
            'referred_by_user_id' => $this->referred_by_user_id,
        ]);
    }
}
