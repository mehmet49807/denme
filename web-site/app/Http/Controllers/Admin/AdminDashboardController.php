<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Report;
use App\Models\Post;
use App\Models\Story;
use App\Models\PremiumSubscription;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers    = User::count();
        $maleCount     = User::where('gender', 'male')->count();
        $femaleCount   = User::where('gender', 'female')->count();
        $newToday      = User::whereDate('created_at', today())->count();
        $newThisWeek   = User::where('created_at', '>=', now()->subDays(7))->count();

        $premiumActive = PremiumSubscription::active()->count();
        $pendingReports = Report::where('status', 'pending')->count();
        $openTickets    = SupportTicket::where('status', 'open')->count();
        $pendingVerify = User::where('photo_verify_status', 'pending')->count();

        $activePosts   = Post::where('is_active', true)->count();
        $activeStories = Story::where('expires_at', '>', now())->count();

        $recentUsers = User::latest()->limit(10)->get(['id', 'username', 'first_name', 'email', 'gender', 'created_at']);
        $recentReports = Report::with(['reporter', 'reported'])
            ->where('status', 'pending')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'maleCount', 'femaleCount', 'newToday', 'newThisWeek',
            'premiumActive', 'pendingReports', 'openTickets', 'pendingVerify',
            'activePosts', 'activeStories', 'recentUsers', 'recentReports'
        ));
    }
}
