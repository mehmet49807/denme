<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBroadcastRead extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'broadcast_id', 'user_id', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function broadcast()
    {
        return $this->belongsTo(AdminBroadcast::class, 'broadcast_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

