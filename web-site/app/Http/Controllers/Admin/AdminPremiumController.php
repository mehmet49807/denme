<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremiumSubscription;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPremiumController extends Controller
{
    public function index(Request $request)
    {
        $query = PremiumSubscription::with('user');

        if ($package = $request->get('package_type')) {
            $query->where('package_type', $package);
        }

        if ($request->get('active') === 'yes') {
            $query->where('is_active', true);
        } elseif ($request->get('active') === 'no') {
            $query->where('is_active', false);
        }

        $subscriptions = $query->latest()->paginate(20);
        $activeCount = PremiumSubscription::active()->count();
        $totalRevenue = PremiumSubscription::where('is_active', true)->sum('price_tl');

        return view('admin.premium.index', compact('subscriptions', 'activeCount', 'totalRevenue'));
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'package_type'  => 'required|in:pro,gold,platinum',
            'duration_days' => 'nullable|integer|min:1|max:365',
        ]);

        $package = PremiumSubscription::PACKAGES[$validated['package_type']];
        $days = $validated['duration_days'] ?? $package['duration_days'];

        PremiumSubscription::create([
            'user_id'           => $validated['user_id'],
            'package_type'      => $validated['package_type'],
            'price_tl'          => $package['price_tl'],
            'duration_days'     => $days,
            'starts_at'         => now(),
            'expires_at'        => now()->addDays($days),
            'is_active'         => true,
            'payment_reference' => 'admin_assign_' . $request->user()->id,
        ]);

        return back()->with('success', 'Premium paket atandı.');
    }
}
