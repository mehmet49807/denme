<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'user_id', 'image_url', 'caption', 'likes_count', 'is_active',
    ];

    protected static function booted(): void
    {
        static::created(function (Post $post) {
            if (class_exists(\App\Jobs\RunAiModerationJob::class)) {
                \App\Jobs\RunAiModerationJob::dispatchAfterResponse('post', $post->id);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function toFeedArray(?int $viewerId = null, ?bool $isLiked = null): array
    {
        if ($isLiked === null && $viewerId) {
            $isLiked = $this->relationLoaded('likes')
                ? $this->likes->contains('user_id', $viewerId)
                : $this->likes()->where('user_id', $viewerId)->exists();
        }

        return [
            'id' => $this->id,
            'username' => $this->user->username,
            'country' => $this->user->country ?? 'Türkiye',
            'city' => $this->user->city,
            'district' => $this->user->district,
            'image_url' => $this->image_url,
            'caption' => $this->caption,
            'likes_count' => $this->likes_count,
            'is_liked' => $isLiked,
            'comments_enabled' => false,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
