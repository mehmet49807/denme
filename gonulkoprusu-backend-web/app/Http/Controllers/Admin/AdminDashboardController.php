<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\PremiumSubscription;
use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users'          => User::count(),
            'men'            => User::where('gender', 'male')->count(),
            'women'          => User::where('gender', 'female')->count(),
            'premium'        => User::where('is_premium', true)->count(),
            'pending_reports'=> Report::where('status', 'pending')->count(),
            'messages'       => Message::count(),
            'revenue'        => PremiumSubscription::where('is_active', true)->sum('price'),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
