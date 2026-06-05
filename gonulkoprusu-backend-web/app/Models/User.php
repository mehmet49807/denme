<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username', 'first_name', 'last_name', 'email', 'phone',
        'password', 'gender', 'city', 'district', 'profile_photo',
        'bio', 'role', 'status', 'is_premium',
    ];

    /**
     * PRIVATE fields are hidden from default serialization.
     * They are exposed ONLY through the owner/admin specific resources.
     */
    protected $hidden = [
        'password', 'remember_token', 'first_name', 'last_name',
        'email', 'phone',
    ];

    protected $casts = [
        'is_premium'        => 'boolean',
        'last_login_at'     => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    /* --------------------------------------------------------------- */
    /*  Relationships                                                   */
    /* --------------------------------------------------------------- */

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(PremiumSubscription::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    /* --------------------------------------------------------------- */
    /*  Helpers / Business rules                                       */
    /* --------------------------------------------------------------- */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMale(): bool
    {
        return $this->gender === 'male';
    }

    /** Premium is a MEN-ONLY concept; women always have full access for free. */
    public function hasActivePremium(): bool
    {
        if (! $this->isMale()) {
            return true; // women: full access, no premium tier
        }

        return $this->subscriptions()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /** Only premium men may publish stories. */
    public function canPostStories(): bool
    {
        return $this->isMale() && $this->hasActivePremium();
    }

    /** Straight matching: the opposite gender this user is allowed to browse. */
    public function oppositeGender(): string
    {
        return $this->isMale() ? 'female' : 'male';
    }

    /** IDs this user blocked or was blocked by - excluded from feeds/lists. */
    public function blockedUserIds(): array
    {
        $iBlocked = Block::where('blocker_id', $this->id)->pluck('blocked_id');
        $blockedMe = Block::where('blocked_id', $this->id)->pluck('blocker_id');

        return $iBlocked->merge($blockedMe)->unique()->values()->all();
    }
}
