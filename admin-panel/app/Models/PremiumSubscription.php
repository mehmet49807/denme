<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumSubscription extends Model
{
    protected $fillable = [
        'user_id', 'package_type', 'price_tl', 'duration_days',
        'starts_at', 'expires_at', 'payment_reference', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'price_tl' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public const PACKAGES = [
        'pro' => ['name' => 'Pro', 'duration_days' => 7, 'price_tl' => 250],
        'gold' => ['name' => 'Gold', 'duration_days' => 14, 'price_tl' => 400],
        'platinum' => ['name' => 'Platinum', 'duration_days' => 30, 'price_tl' => 500],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('expires_at', '>', now());
    }
}
