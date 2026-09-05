<?php

namespace App\Http\Controllers\Admin;

use App\Support\PhotoVerification;

use App\Http\Controllers\Controller;
use App\Jobs\SendBroadcastPushJob;
use App\Models\AdminBroadcast;
use App\Models\AdminUserNote;
use App\Models\Message;
use App\Models\PremiumSubscription;
use App\Models\Referral;
use App\Models\Report;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AdminAuditService;
use App\Services\FcmPushService;
use App\Services\NotificationService;
use App\Services\PremiumPackagesService;
use App\Services\UserDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminPanelController extends Controller
{
    private const ONLINE_THRESHOLD_MINUTES = 10;

    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->dashboardStatsData(),
            'chartData' => $this->dashboardChartData(),
            'online' => $this->dashboardOnlineData(),
        ]);
    }

    public function dashboardStats(): JsonResponse
    {
        $payload = Cache::remember('admin_dashboard_full', now()->addMinutes(3), function () {
            return [
                'stats' => $this->dashboardStatsData(false),
                'charts' => $this->dashboardChartData(),
                'updated_at' => now()->format('d.m.Y H:i:s'),
            ];
        });

        $payload['online'] = $this->dashboardOnlineData(false);

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    // IMPORTANT: Full file body is on production FTP.
    // This temporary partial prevents empty class 500s for dashboard only.
    // Complete controller must be synced from /home/workdir/artifacts/AdminPanelController.php

    private function dashboardStatsData(bool $useCache = true): array
    {
        $resolver = function () {
            return [
                'total_users' => User::where('role', 'user')->count(),
                'pending_reports' => Report::where('status', 'pending')->count(),
                'active_premium' => PremiumSubscription::active()->count(),
                'revenue_tl' => (float) PremiumSubscription::active()->sum('price_tl'),
                'active_male' => User::where('role', 'user')->where('is_banned', false)->where('gender', 'male')->count(),
                'active_female' => User::where('role', 'user')->where('is_banned', false)->where('gender', 'female')->count(),
                'total_referrals' => Schema::hasTable('referrals') ? Referral::count() : 0,
                'referred_users' => Schema::hasColumn('users', 'referred_by_user_id')
                    ? User::where('role', 'user')->whereNotNull('referred_by_user_id')->count()
                    : 0,
                'pending_profiles' => 0,
                'open_support' => Schema::hasTable('support_tickets')
                    ? SupportTicket::query()->whereIn('status', ['open', 'pending'])->count()
                    : 0,
                'signups_today' => User::where('role', 'user')->whereDate('created_at', today())->count(),
            ];
        };

        if (! $useCache) {
            return $resolver();
        }

        return Cache::remember('admin_dashboard_stats', now()->addMinutes(3), $resolver);
    }

    private function dashboardChartData(int $days = 14): array
    {
        return [
            'labels' => [],
            'user_signups' => [],
            'messages' => [],
            'premium_sales' => [],
            'gender' => [
                'male' => User::where('role', 'user')->where('is_banned', false)->where('gender', 'male')->count(),
                'female' => User::where('role', 'user')->where('is_banned', false)->where('gender', 'female')->count(),
                'banned' => User::where('role', 'user')->where('is_banned', true)->count(),
            ],
        ];
    }

    private function dashboardOnlineData(bool $useCache = true): array
    {
        return [
            'now' => 0,
            'now_male' => 0,
            'now_female' => 0,
            'periods' => [],
            'daily_labels' => [],
            'daily' => [],
            'threshold_minutes' => self::ONLINE_THRESHOLD_MINUTES,
        ];
    }
}
