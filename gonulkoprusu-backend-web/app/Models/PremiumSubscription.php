<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumSubscription extends Model
{
    protected $fillable = [
        'user_id', 'package_type', 'price', 'started_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
        'price'      => 'decimal:2',
    ];

    /**
     * Single source of truth for the MEN-ONLY package catalog.
     *   pro      => 7  days  => 250 TL
     *   gold     => 14 days  => 300 TL
     *   platinum => 30 days  => 500 TL
     */
    public const PACKAGES = [
        'pro'      => ['days' => 7,  'price' => 250.00, 'label' => 'Pro - 1 Hafta'],
        'gold'     => ['days' => 14, 'price' => 300.00, 'label' => 'Gold - 2 Hafta'],
        'platinum' => ['days' => 30, 'price' => 500.00, 'label' => 'Platinum - 1 Ay'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
