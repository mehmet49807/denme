<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PremiumPackagesService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PremiumPageController extends Controller
{
    public function index(Request $request, PremiumPackagesService $packages): View
    {
        $user = $request->user();
        $activeSubscription = null;

        if ($user->gender === 'male') {
            $activeSubscription = $user->premiumSubscriptions()
                ->active()
                ->latest('expires_at')
                ->first();
        }

        return view('web.premium', [
            'packages' => $packages->catalog(),
            'featuredPackage' => $packages->featuredType(),
            'features' => $this->packageFeatures(),
            'user' => $user,
            'activeSubscription' => $activeSubscription,
        ]);
    }

    private function packageFeatures(): array
    {
        return [
            __('app.premium.perk_unlimited_messages'),
            __('app.premium.perk_gallery'),
            __('app.premium.perk_featured_profile'),
            __('app.premium.perk_stories'),
            __('app.premium.perk_who_viewed'),
            __('app.premium.perk_top_featured'),
        ];
    }
}
