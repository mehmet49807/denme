<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumController extends Controller
{
    public const PACKAGES = [
        'pro' => ['label' => 'Pro', 'duration_days' => 7, 'price_try' => 250],
        'gold' => ['label' => 'Gold', 'duration_days' => 14, 'price_try' => 300],
        'platinum' => ['label' => 'Platinum', 'duration_days' => 30, 'price_try' => 500],
    ];

    public function packages(): JsonResponse
    {
        return response()->json(['data' => collect(self::PACKAGES)->map(fn ($package, $type) => ['type' => $type] + $package)->values()]);
    }

    public function status(Request $request): JsonResponse
    {
        return response()->json($request->user()->premiumStatus());
    }

    public function subscribe(Request $request): JsonResponse
    {
        abort_if($request->user()->gender !== 'male', 403, 'Premium subscriptions apply only to male accounts.');

        $data = $request->validate([
            'package_type' => ['required', 'in:pro,gold,platinum'],
            'payment_token' => ['required', 'string', 'max:255'],
        ]);

        $package = self::PACKAGES[$data['package_type']];
        $request->user()->premiumSubscriptions()->create([
            'package_type' => $data['package_type'],
            'price_try' => $package['price_try'],
            'expires_at' => now()->addDays($package['duration_days']),
            'provider_reference' => 'placeholder:' . substr($data['payment_token'], 0, 32),
        ]);

        return response()->json($request->user()->premiumStatus(), 201);
    }
}
