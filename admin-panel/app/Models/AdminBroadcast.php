<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminBroadcast extends Model
{
    public const TTL_HOURS = 24;

    public $timestamps = false;

    protected $fillable = [
        'admin_id', 'title', 'message_text', 'target_gender', 'sent_count',
        'status', 'scheduled_at', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('target_gender', 'all')
                ->orWhere('target_gender', $user->gender);
        });
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subHours(self::TTL_HOURS));
    }

    public function scopeDueScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }
}
