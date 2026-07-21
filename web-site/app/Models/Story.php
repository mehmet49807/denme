<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'media_url', 'media_type', 'expires_at', 'created_at',
    ];

    protected static function booted(): void
    {
        static::created(function (Story $story) {
            if (class_exists(\App\Jobs\RunAiModerationJob::class)) {
                \App\Jobs\RunAiModerationJob::dispatchAfterResponse('story', $story->id);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        // users join'larında belirsizliği önlemek için tablo öneki zorunlu.
        return $query->where(function ($query) {
            $query->where('stories.expires_at', '>', now())
                ->orWhere(function ($nested) {
                    $nested->whereNull('stories.expires_at')
                        ->where('stories.created_at', '>', now()->subHours(24));
                });
        });
    }

    public function getIsVideoAttribute(): bool
    {
        if ($this->media_type === 'video') {
            return true;
        }

        return (bool) preg_match('/\.(mp4|webm|mov)(\?|$)/i', (string) $this->media_url);
    }
}
