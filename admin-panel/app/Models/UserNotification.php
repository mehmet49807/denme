<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    public const TYPE_POST_LIKE = 'post_like';
    public const TYPE_NEW_MESSAGE = 'new_message';
    public const TYPE_REPORT_UPDATE = 'report_update';
    public const TYPE_MODERATION = 'moderation';
    public const TYPE_ADMIN_NOTICE = 'admin_notice';

    public const TTL_HOURS = 24;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'body',
        'post_id',
        'like_id',
        'message_id',
        'report_id',
        'read_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subHours(self::TTL_HOURS));
    }
}
