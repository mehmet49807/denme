<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PremiumSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PremiumController extends Controller
{
    /** Public catalog (men only). */
    public function packages(): JsonResponse
    {
        return response()->json(['packages' => PremiumSubscription::PACKAGES]);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $subscription = $user->subscriptions()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        return response()->json([
            'is_premium'   => $user->hasActivePremium(),
            'subscription' => $subscription,
        ]);
    }

    /**
     * Purchase / activate a package.
     * Premium is MEN-ONLY: women already have full free access.
     * NOTE: payment gateway integration is a placeholder for now.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isMale()) {
            return response()->json([
                'message' => 'Premium yalnızca erkek hesaplar içindir. Kadın hesaplar ücretsiz tam erişime sahiptir.',
            ], 422);
        }

        $request->validate([
            'package_type' => ['required', Rule::in(array_keys(PremiumSubscription::PACKAGES))],
        ]);

        $package = PremiumSubscription::PACKAGES[$request->package_type];

        $subscription = DB::transaction(function () use ($user, $request, $package) {
            // Deactivate any previous active subscriptions.
            $user->subscriptions()->where('is_active', true)->update(['is_active' => false]);

            $sub = $user->subscriptions()->create([
                'package_type' => $request->package_type,
                'price'        => $package['price'],
                'started_at'   => now(),
                'expires_at'   => now()->addDays($package['days']),
                'is_active'    => true,
            ]);

            $user->update(['is_premium' => true]);

            return $sub;
        });

        return response()->json([
            'message'      => 'Premium üyeliğiniz etkinleştirildi.',
            'subscription' => $subscription,
        ], 201);
    }
}
