<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminReferralController extends Controller
{
    public function index(): View
    {
        $tableReady = Schema::hasTable('referrals');

        $stats = [
            'total_referrals' => $tableReady ? Referral::count() : 0,
            'users_with_code' => Schema::hasColumn('users', 'referral_code')
                ? User::where('role', 'user')->whereNotNull('referral_code')->count()
                : 0,
            'referred_users' => Schema::hasColumn('users', 'referred_by_user_id')
                ? User::where('role', 'user')->whereNotNull('referred_by_user_id')->count()
                : 0,
        ];

        $topReferrers = $tableReady
            ? Referral::query()
                ->select('referrer_id', DB::raw('COUNT(*) as total'))
                ->groupBy('referrer_id')
                ->orderByDesc('total')
                ->limit(10)
                ->with('referrer:id,username,first_name,email')
                ->get()
            : collect();

        $recentReferrals = $tableReady
            ? Referral::with(['referrer:id,username', 'referred:id,username,created_at'])
                ->latest('created_at')
                ->limit(20)
                ->get()
            : collect();

        $utmBreakdown = Schema::hasColumn('users', 'utm_source')
            ? User::query()
                ->where('role', 'user')
                ->whereNotNull('utm_source')
                ->select('utm_source', 'utm_medium', 'utm_campaign', DB::raw('COUNT(*) as total'))
                ->groupBy('utm_source', 'utm_medium', 'utm_campaign')
                ->orderByDesc('total')
                ->limit(15)
                ->get()
            : collect();

        $registrationSources = Schema::hasColumn('users', 'registration_source')
            ? User::query()
                ->where('role', 'user')
                ->whereNotNull('registration_source')
                ->select('registration_source', DB::raw('COUNT(*) as total'))
                ->groupBy('registration_source')
                ->orderByDesc('total')
                ->get()
            : collect();

        return view('admin.referrals', compact(
            'tableReady',
            'stats',
            'topReferrers',
            'recentReferrals',
            'utmBreakdown',
            'registrationSources',
        ));
    }
}
