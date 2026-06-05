<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'username', 'first_name', 'last_name', 'email', 'password', 'phone', 'gender',
        'profile_photo_url', 'city', 'district', 'role', 'status',
    ];

    protected $hidden = ['password'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function blockedBy(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }

    public function reportsAgainst(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_id');
    }

    public function premiumSubscriptions(): HasMany
    {
        return $this->hasMany(PremiumSubscription::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function toPublicArray(): array
    {
        return $this->only(['id', 'username', 'profile_photo_url', 'gender', 'city', 'district']);
    }

    public function toOwnerArray(): array
    {
        return $this->only(['id', 'username', 'first_name', 'last_name', 'email', 'phone', 'gender', 'profile_photo_url', 'city', 'district']);
    }

    public function canViewPublicProfile(User $target): bool
    {
        if ($this->id === $target->id || $this->role === 'admin') {
            return true;
        }

        if ($target->status !== 'active') {
            return false;
        }

        if ($this->blocks()->where('blocked_id', $target->id)->exists()) {
            return false;
        }

        if ($this->blockedBy()->where('blocker_id', $target->id)->exists()) {
            return false;
        }

        return $this->gender !== $target->gender;
    }

    public function hasActivePremium(): bool
    {
        return $this->premiumSubscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function premiumStatus(): array
    {
        $subscription = $this->premiumSubscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        return [
            'is_premium' => (bool) $subscription,
            'package_type' => $subscription?->package_type,
            'expires_at' => $subscription?->expires_at,
        ];
    }
}
