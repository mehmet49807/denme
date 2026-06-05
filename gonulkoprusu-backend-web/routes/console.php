<?php

use Illuminate\Support\Facades\Schedule;

// Expire premium subscriptions whose period has elapsed (men only).
Schedule::call(function () {
    \App\Models\PremiumSubscription::where('is_active', true)
        ->where('expires_at', '<=', now())
        ->update(['is_active' => false]);

    \App\Models\User::where('is_premium', true)
        ->whereDoesntHave('subscriptions', fn ($q) => $q->where('is_active', true)->where('expires_at', '>', now()))
        ->where('gender', 'male')
        ->update(['is_premium' => false]);
})->daily();
