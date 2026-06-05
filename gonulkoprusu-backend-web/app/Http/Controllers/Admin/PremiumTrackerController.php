<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremiumSubscription;
use Illuminate\Contracts\View\View;

class PremiumTrackerController extends Controller
{
    public function index(): View
    {
        $active = PremiumSubscription::with('user')
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->paginate(25);

        $distribution = PremiumSubscription::selectRaw('package_type, count(*) as total, sum(price) as revenue')
            ->where('is_active', true)
            ->groupBy('package_type')
            ->get()
            ->keyBy('package_type');

        $totalRevenue = PremiumSubscription::sum('price');

        return view('admin.premium.index', compact('active', 'distribution', 'totalRevenue'));
    }
}
