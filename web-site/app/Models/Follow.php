<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class Follow extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'follower_id',
        'following_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public static function ensureTable(): bool
    {
        if (Schema::hasTable('follows')) {
            return true;
        }

        try {
            Schema::create('follows', function ($table) {
                $table->id();
                $table->unsignedBigInteger('follower_id');
                $table->unsignedBigInteger('following_id');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['follower_id', 'following_id'], 'follows_pair_uidx');
                $table->index(['following_id', 'created_at'], 'follows_following_created_idx');
                $table->index(['follower_id', 'created_at'], 'follows_follower_created_idx');
            });

            return true;
        } catch (\Throwable) {
            return Schema::hasTable('follows');
        }
    }

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    public static function isFollowing(int $followerId, int $followingId): bool
    {
        if ($followerId <= 0 || $followingId <= 0 || ! self::ensureTable()) {
            return false;
        }

        return self::query()
            ->where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->exists();
    }

    public static function isMutual(int $a, int $b): bool
    {
        return self::isFollowing($a, $b) && self::isFollowing($b, $a);
    }

    /**
     * @return Collection<int, int>
     */
    public static function followingIdsFor(int $userId): Collection
    {
        if ($userId <= 0 || ! self::ensureTable()) {
            return collect();
        }

        return self::query()
            ->where('follower_id', $userId)
            ->pluck('following_id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }
}
