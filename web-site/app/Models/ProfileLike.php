<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ProfileLike extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'liker_id',
        'liked_id',
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
        if (Schema::hasTable('profile_likes')) {
            return true;
        }

        try {
            Schema::create('profile_likes', function ($table) {
                $table->id();
                $table->unsignedBigInteger('liker_id');
                $table->unsignedBigInteger('liked_id');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['liker_id', 'liked_id'], 'profile_likes_pair_uidx');
                $table->index(['liked_id', 'created_at'], 'profile_likes_liked_created_idx');
            });

            return true;
        } catch (\Throwable) {
            return Schema::hasTable('profile_likes');
        }
    }

    public function liker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liker_id');
    }

    public function liked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liked_id');
    }

    public static function isMutual(int $a, int $b): bool
    {
        if (! self::ensureTable()) {
            return false;
        }

        return self::query()->where('liker_id', $a)->where('liked_id', $b)->exists()
            && self::query()->where('liker_id', $b)->where('liked_id', $a)->exists();
    }
}
