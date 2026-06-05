<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = ['image_url', 'likes_count'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function toFeedArray(User $viewer): array
    {
        return [
            'id' => $this->id,
            'author' => $this->user->toPublicArray(),
            'image_url' => $this->image_url,
            'likes_count' => $this->likes_count,
            'liked_by_me' => $this->likes()->where('user_id', $viewer->id)->exists(),
            'comments_enabled' => false,
            'created_at' => $this->created_at,
        ];
    }
}
