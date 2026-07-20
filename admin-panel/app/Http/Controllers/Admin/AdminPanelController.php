<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBroadcastPushJob;
use App\Models\AdminBroadcast;
use App\Models\AdminUserNote;
use App\Models\AiModerationFlag;
use App\Models\Message;
use App\Models\PremiumSubscription;
use App\Models\Referral;
use App\Models\Report;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AdminAuditService;
use App\Services\FcmPushService;
use App\Services\NotificationService;
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

    private function dashboardStatsData(bool $useCache = true): array
    {
        $resolver = function () {
            $referralsTableReady = Schema::hasTable('referrals');
            $usersHasReferralOwner = Schema::hasColumn('users', 'referred_by_user_id');

            $pendingProfiles = Schema::hasColumn('users', 'profile_verified_at')
                ? User::where('role', 'user')
                    ->whereNull('profile_verified_at')
                    ->whereNotNull('profile_photo_url')
                    ->where('profile_photo_url', '!=', '')
                    ->count()
                : 0;

            $aiFlags = Schema::hasTable('ai_moderation_flags')
                ? AiModerationFlag::where('status', AiModerationFlag::STATUS_PENDING)->count()
                : 0;

            $openSupport = Schema::hasTable('support_tickets')
                ? SupportTicket::query()->whereIn('status', ['open', 'pending'])->count()
                : 0;

            $signupsToday = User::where('role', 'user')->whereDate('created_at', today())->count();

            return [
                'total_users' => User::where('role', 'user')->count(),
                'pending_reports' => Report::where('status', 'pending')->count(),
                'active_premium' => PremiumSubscription::active()->count(),
                'revenue_tl' => (float) PremiumSubscription::active()->sum('price_tl'),
                'active_male' => $this->activeUsersQuery('male')->count(),
                'active_female' => $this->activeUsersQuery('female')->count(),
                'total_referrals' => $referralsTableReady ? Referral::count() : 0,
                'referred_users' => $usersHasReferralOwner
                    ? User::where('role', 'user')->whereNotNull('referred_by_user_id')->count()
                    : 0,
                'pending_profiles' => $pendingProfiles,
                'ai_flags' => $aiFlags,
                'open_support' => $openSupport,
                'signups_today' => $signupsToday,
            ];
        };

        if (! $useCache) {
            return $resolver();
        }

        return Cache::remember('admin_dashboard_stats', now()->addMinutes(3), $resolver);
    }

    private function dashboardChartData(int $days = 14): array
    {
        return Cache::remember('admin_dashboard_charts', now()->addMinutes(3), function () use ($days) {
            return [
                'labels' => $this->chartDayLabels($days),
                'user_signups' => $this->dailyCounts(User::class, $days, ['role' => 'user']),
                'messages' => $this->dailyCounts(Message::class, $days),
                'premium_sales' => $this->dailyCounts(PremiumSubscription::class, $days),
                'gender' => [
                    'male' => $this->activeUsersQuery('male')->count(),
                    'female' => $this->activeUsersQuery('female')->count(),
                    'banned' => User::where('role', 'user')->where('is_banned', true)->count(),
                ],
            ];
        });
    }

    private function activeUsersQuery(string $gender)
    {
        return User::where('role', 'user')
            ->where('is_banned', false)
            ->where('gender', $gender);
    }

    private function dashboardOnlineData(bool $useCache = true): array
    {
        $resolver = function () {
            $now = now();
            $onlineSince = $now->copy()->subMinutes(self::ONLINE_THRESHOLD_MINUTES);

            $periods = [
                'today' => $this->countActiveUsers($now->copy()->startOfDay()),
                'yesterday' => $this->countActiveUsers(
                    $now->copy()->subDay()->startOfDay(),
                    $now->copy()->startOfDay()
                ),
                'this_week' => $this->countActiveUsers($now->copy()->startOfWeek()),
                'last_week' => $this->countActiveUsers(
                    $now->copy()->subWeek()->startOfWeek(),
                    $now->copy()->startOfWeek()
                ),
                'this_month' => $this->countActiveUsers($now->copy()->startOfMonth()),
                'last_month' => $this->countActiveUsers(
                    $now->copy()->subMonth()->startOfMonth(),
                    $now->copy()->startOfMonth()
                ),
            ];

            $days = 14;

            return [
                'now' => $this->countActiveUsers($onlineSince),
                'now_male' => $this->countActiveUsers($onlineSince, null, 'male'),
                'now_female' => $this->countActiveUsers($onlineSince, null, 'female'),
                'periods' => $periods,
                'daily_labels' => $this->chartDayLabels($days),
                'daily' => $this->dailyActiveUserCounts($days),
                'threshold_minutes' => self::ONLINE_THRESHOLD_MINUTES,
            ];
        };

        if (! $useCache) {
            return $resolver();
        }

        return Cache::remember('admin_dashboard_online', now()->addMinute(), $resolver);
    }

    private function onlineUsersBaseQuery()
    {
        return User::where('role', 'user')
            ->where('is_banned', false)
            ->whereNotNull('last_active_at');
    }

    private function countActiveUsers(?\Illuminate\Support\Carbon $since = null, ?\Illuminate\Support\Carbon $until = null, ?string $gender = null): int
    {
        $query = $this->onlineUsersBaseQuery();

        if ($since) {
            $query->where('last_active_at', '>=', $since);
        }

        if ($until) {
            $query->where('last_active_at', '<', $until);
        }

        if ($gender) {
            $query->where('gender', $gender);
        }

        return $query->count();
    }

    private function dailyActiveUserCounts(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $raw = $this->onlineUsersBaseQuery()
            ->where('last_active_at', '>=', $start)
            ->selectRaw('DATE(last_active_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $counts = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $counts[] = (int) ($raw[$day] ?? 0);
        }

        return $counts;
    }

    private function chartDayLabels(int $days): array
    {
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = now()->subDays($i)->format('d.m');
        }

        return $labels;
    }

    private function dailyCounts(string $model, int $days, array $where = []): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $query = $model::where('created_at', '>=', $start);

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        $raw = $query
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $counts = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $counts[] = (int) ($raw[$day] ?? 0);
        }

        return $counts;
    }

    public function users(Request $request): View
    {
        $referralsTableReady = Schema::hasTable('referrals');
        $usersHasReferralOwner = Schema::hasColumn('users', 'referred_by_user_id');
        $query = User::query()
            ->where('role', 'user')
            ->with('referredBy:id,username');

        if ($referralsTableReady) {
            $query->withCount('referralsMade');
        }

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($gender = $request->get('gender')) {
            if (in_array($gender, ['male', 'female'], true)) {
                $query->where('gender', $gender);
            }
        }

        if ($status = $request->get('status')) {
            if ($status === 'banned') {
                $query->where('is_banned', true);
            } elseif ($status === 'active') {
                $query->where('is_banned', false);
            }
        }

        $stats = [
            'total' => User::where('role', 'user')->count(),
            'active' => User::where('role', 'user')->where('is_banned', false)->count(),
            'banned' => User::where('role', 'user')->where('is_banned', true)->count(),
            'male' => User::where('role', 'user')->where('gender', 'male')->count(),
            'female' => User::where('role', 'user')->where('gender', 'female')->count(),
            'invited_total' => $referralsTableReady ? Referral::count() : 0,
            'referred_total' => $usersHasReferralOwner
                ? User::where('role', 'user')->whereNotNull('referred_by_user_id')->count()
                : 0,
        ];

        $users = $query->with(['premiumSubscriptions' => function ($q) {
            $q->where('is_active', true)->where('expires_at', '>', now())->latest('expires_at');
        }])->latest()->paginate(20)->withQueryString();

        $userNotesPayload = [];
        app(AdminAuditService::class)->ensureTables();
        if (Schema::hasTable('admin_user_notes')) {
            $ids = $users->getCollection()->pluck('id')->all();
            $grouped = AdminUserNote::query()
                ->with('admin:id,username')
                ->whereIn('user_id', $ids)
                ->latest()
                ->get()
                ->groupBy('user_id');

            foreach ($grouped as $userId => $notes) {
                $userNotesPayload[(string) $userId] = $notes->map(fn (AdminUserNote $n) => [
                    'id' => $n->id,
                    'note' => $n->note,
                    'admin' => $n->admin->username ?? 'admin',
                    'pinned' => (bool) $n->is_pinned,
                    'at' => optional($n->created_at)->format('d.m.Y H:i'),
                ])->values()->all();
            }
        }

        return view('admin.users', [
            'users' => $users,
            'stats' => $stats,
            'premiumPackages' => PremiumSubscription::PACKAGES,
            'referralsTableReady' => $referralsTableReady,
            'userNotesPayload' => $userNotesPayload,
        ]);
    }

    public function profileApprovals(Request $request): View
    {
        $this->ensureProfileApprovalColumns();

        $baseQuery = User::query()
            ->where('role', 'user')
            ->where('is_banned', false);

        $stats = [
            'pending' => (clone $baseQuery)->whereNull('profile_verified_at')->count(),
            'verified' => (clone $baseQuery)->whereNotNull('profile_verified_at')->count(),
            'with_photo_pending' => (clone $baseQuery)
                ->whereNull('profile_verified_at')
                ->whereNotNull('profile_photo_url')
                ->where('profile_photo_url', '!=', '')
                ->count(),
            'no_photo' => (clone $baseQuery)->where(function ($query) {
                $query->whereNull('profile_photo_url')->orWhere('profile_photo_url', '');
            })->count(),
        ];

        $status = $request->get('status', 'pending');
        $query = (clone $baseQuery);

        if ($status === 'verified') {
            $query->whereNotNull('profile_verified_at');
        } elseif ($status === 'with_photo') {
            $query->whereNull('profile_verified_at')
                ->whereNotNull('profile_photo_url')
                ->where('profile_photo_url', '!=', '');
        } elseif ($status === 'no_photo') {
            $query->whereNull('profile_verified_at')
                ->where(function ($inner) {
                    $inner->whereNull('profile_photo_url')->orWhere('profile_photo_url', '');
                });
        } else {
            $status = 'pending';
            $query->whereNull('profile_verified_at');
        }

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(30)->withQueryString();

        return view('admin.profile-approvals', [
            'users' => $users,
            'stats' => $stats,
            'status' => $status,
            'search' => $search ?? '',
        ]);
    }

    public function approveProfile(Request $request, User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        $this->ensureProfileApprovalColumns();

        $user->forceFill([
            'profile_verified_at' => now(),
            'profile_verified_by' => $request->user()->id,
            'profile_verification_note' => Str::limit((string) $request->input('note', 'Admin profil onayı'), 250, ''),
        ])->save();

        return back()->with('success', $user->username.' profili doğrulandı.');
    }

    public function bulkApproveProfiles(Request $request)
    {
        $this->ensureProfileApprovalColumns();

        $limit = max(1, min(100, (int) $request->input('limit', 25)));
        $users = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->whereNull('profile_verified_at')
            ->whereNotNull('profile_photo_url')
            ->where('profile_photo_url', '!=', '')
            ->latest()
            ->limit($limit)
            ->get();

        foreach ($users as $user) {
            $user->forceFill([
                'profile_verified_at' => now(),
                'profile_verified_by' => $request->user()->id,
                'profile_verification_note' => 'Hızlı toplu profil onayı',
            ])->save();
        }

        return back()->with('success', $users->count().' fotoğraflı profil hızlı onaylandı.');
    }

    private function ensureProfileApprovalColumns(): void
    {
        if (! Schema::hasColumn('users', 'profile_verified_at')) {
            Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->timestamp('profile_verified_at')->nullable()->after('profile_photo_url');
            });
        }

        if (! Schema::hasColumn('users', 'profile_verified_by')) {
            Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->unsignedBigInteger('profile_verified_by')->nullable()->after('profile_verified_at');
            });
        }

        if (! Schema::hasColumn('users', 'profile_verification_note')) {
            Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('profile_verification_note', 255)->nullable()->after('profile_verified_by');
            });
        }
    }

    public function messages(Request $request): View
    {
        $username = trim((string) $request->get('username', ''));
        $keyword = trim((string) $request->get('keyword', ''));
        $dateFrom = trim((string) $request->get('date_from', ''));
        $dateTo = trim((string) $request->get('date_to', ''));

        $base = DB::table('messages');

        if ($username !== '') {
            $matchedIds = User::query()
                ->where('username', 'like', "%{$username}%")
                ->limit(50)
                ->pluck('id');
            if ($matchedIds->isEmpty()) {
                $base->whereRaw('1 = 0');
            } else {
                $base->where(function ($q) use ($matchedIds) {
                    $q->whereIn('sender_id', $matchedIds)->orWhereIn('receiver_id', $matchedIds);
                });
            }
        }

        if ($keyword !== '') {
            $base->where('message_text', 'like', "%{$keyword}%");
        }

        if ($dateFrom !== '') {
            $base->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $base->whereDate('created_at', '<=', $dateTo);
        }

        $threadPaginator = (clone $base)
            ->selectRaw('CASE WHEN sender_id < receiver_id THEN sender_id ELSE receiver_id END AS user_a_id')
            ->selectRaw('CASE WHEN sender_id < receiver_id THEN receiver_id ELSE sender_id END AS user_b_id')
            ->selectRaw('MAX(id) AS latest_message_id')
            ->selectRaw('MAX(created_at) AS last_at')
            ->selectRaw('COUNT(*) AS message_count')
            ->groupBy('user_a_id', 'user_b_id')
            ->orderByDesc('last_at')
            ->paginate(30)
            ->withQueryString();

        $rows = collect($threadPaginator->items());
        $userIds = $rows->flatMap(fn ($row) => [(int) $row->user_a_id, (int) $row->user_b_id])->unique()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $threads = $rows->map(function ($row) use ($users, $keyword) {
            $userA = $users->get((int) $row->user_a_id);
            $userB = $users->get((int) $row->user_b_id);

            $messagesQuery = Message::with(['sender', 'receiver'])
                ->where(function ($query) use ($row) {
                    $query->where(function ($inner) use ($row) {
                        $inner->where('sender_id', $row->user_a_id)->where('receiver_id', $row->user_b_id);
                    })->orWhere(function ($inner) use ($row) {
                        $inner->where('sender_id', $row->user_b_id)->where('receiver_id', $row->user_a_id);
                    });
                });

            if ($keyword !== '') {
                $messagesQuery->where('message_text', 'like', "%{$keyword}%");
            }

            $messages = $messagesQuery
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->sortBy('created_at')
                ->values();

            return [
                'user_a' => $userA,
                'user_b' => $userB,
                'messages' => $messages,
                'count' => (int) $row->message_count,
                'last_at' => $row->last_at ? \Illuminate\Support\Carbon::parse($row->last_at) : null,
            ];
        });

        return view('admin.messages', [
            'threads' => $threads,
            'threadPaginator' => $threadPaginator,
            'totalMessages' => Message::count(),
            'filters' => [
                'username' => $username,
                'keyword' => $keyword,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function reports(): View
    {
        $reports = Report::with(['reporter', 'reported'])->latest()->paginate(20);

        return view('admin.reports', [
            'reports' => $reports,
            'totalReports' => Report::count(),
            'pendingReports' => Report::where('status', 'pending')->count(),
        ]);
    }

    public function premium(): View
    {
        $tierDistribution = PremiumSubscription::active()
            ->select('package_type', DB::raw('count(*) as count'))
            ->groupBy('package_type')
            ->pluck('count', 'package_type');

        return view('admin.premium', [
            'activeCount' => PremiumSubscription::active()->count(),
            'tierDistribution' => $tierDistribution,
            'totalRevenue' => PremiumSubscription::sum('price_tl'),
            'subscriptions' => PremiumSubscription::with('user')->latest()->paginate(20),
        ]);
    }

    public function broadcasts(): View
    {
        app(AdminAuditService::class)->ensureBroadcastColumns();
        $this->dispatchDueBroadcasts();

        $broadcasts = AdminBroadcast::with('admin')->latest()->paginate(20);
        $fcm = app(FcmPushService::class);

        return view('admin.broadcasts', [
            'broadcasts' => $broadcasts,
            'fcmConfigured' => $fcm->isConfigured(),
            'registeredDevices' => $fcm->registeredDeviceCount(),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username,'.$user->id.'|regex:/^[a-zA-Z0-9_]+$/',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'is_banned' => 'nullable|boolean',
            'banned_reason' => 'nullable|string|max:500',
        ]);

        $wasBanned = (bool) $user->is_banned;
        $isBanned = $request->boolean('is_banned');

        $user->update([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'city' => $request->city,
            'district' => $request->district,
            'is_banned' => $isBanned,
            'banned_at' => $isBanned ? now() : null,
            'banned_reason' => $isBanned ? $request->banned_reason : null,
        ]);

        if ($isBanned && ! $wasBanned) {
            app(AdminAuditService::class)->log('user.ban', $user->username.' banlandı', 'user', (int) $user->id, [
                'reason' => $request->banned_reason,
            ]);
        } elseif (! $isBanned && $wasBanned) {
            app(AdminAuditService::class)->log('user.unban', $user->username.' banı kaldırıldı', 'user', (int) $user->id);
        } else {
            app(AdminAuditService::class)->log('user.update', $user->username.' güncellendi', 'user', (int) $user->id);
        }

        return redirect()->route('admin.users')->with('success', 'Kullanıcı güncellendi.');
    }

    public function unbanUser(User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        $user->update([
            'is_banned' => false,
            'banned_at' => null,
            'banned_reason' => null,
        ]);

        app(AdminAuditService::class)->log('user.unban', $user->username.' banı kaldırıldı', 'user', (int) $user->id);

        return redirect()->route('admin.users')->with('success', 'Kullanıcının banı kaldırıldı.');
    }

    public function destroyUser(Request $request, User $user, UserDeletionService $deletion)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        if ((int) $user->id === (int) $request->user()->id) {
            return back()->withErrors(['user' => 'Kendi hesabınızı bu ekrandan silemezsiniz.']);
        }

        $username = $user->username;

        try {
            $deletion->delete($user);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['user' => $e->getMessage()]);
        } catch (\Throwable) {
            return back()->withErrors(['user' => 'Kullanıcı silinemedi. Lütfen tekrar deneyin.']);
        }

        app(AdminAuditService::class)->log('user.delete', $username.' kalıcı silindi', 'user', (int) $user->id);
        $this->forgetDashboardCache();

        return redirect()
            ->route('admin.users', $request->only(['search', 'gender', 'status', 'page']))
            ->with('success', $username.' kalıcı olarak silindi.');
    }

    public function bulkUserAction(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'bulk_action' => 'required|in:ban,unban,verify',
            'banned_reason' => 'nullable|string|max:500',
        ]);

        $users = User::query()
            ->where('role', 'user')
            ->whereIn('id', $validated['user_ids'])
            ->get();

        if ($users->isEmpty()) {
            return back()->withErrors(['user_ids' => 'Geçerli kullanıcı bulunamadı.']);
        }

        $this->ensureProfileApprovalColumns();
        $count = 0;

        foreach ($users as $user) {
            if ($validated['bulk_action'] === 'ban') {
                $user->update([
                    'is_banned' => true,
                    'banned_at' => now(),
                    'banned_reason' => $validated['banned_reason'] ?? 'Toplu ban',
                ]);
                $count++;
            } elseif ($validated['bulk_action'] === 'unban') {
                $user->update([
                    'is_banned' => false,
                    'banned_at' => null,
                    'banned_reason' => null,
                ]);
                $count++;
            } elseif ($validated['bulk_action'] === 'verify') {
                $user->update([
                    'profile_verified_at' => now(),
                    'profile_verified_by' => $request->user()->id,
                    'profile_verification_note' => 'Toplu onay',
                ]);
                $count++;
            }
        }

        app(AdminAuditService::class)->log(
            'user.bulk.'.$validated['bulk_action'],
            "{$count} kullanıcıya toplu {$validated['bulk_action']}",
            'user',
            null,
            ['user_ids' => $validated['user_ids']],
        );

        $this->forgetDashboardCache();

        $labels = [
            'ban' => 'banlandı',
            'unban' => 'banı kaldırıldı',
            'verify' => 'profili onaylandı',
        ];

        return redirect()->route('admin.users', $request->only(['search', 'gender', 'status']))
            ->with('success', "{$count} kullanıcı {$labels[$validated['bulk_action']]}.");
    }

    public function grantPremium(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'package_type' => 'required|in:pro,gold,platinum',
        ], [
            'user_ids.required' => 'En az bir kullanıcı seçin.',
            'package_type.required' => 'Premium paketi seçin.',
        ]);

        $users = User::query()
            ->where('role', 'user')
            ->whereIn('id', $validated['user_ids'])
            ->get();

        if ($users->isEmpty()) {
            return back()->withErrors(['user_ids' => 'Geçerli kullanıcı bulunamadı.']);
        }

        $granted = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if ($user->gender !== 'male') {
                $skipped++;
                continue;
            }

            $this->applyPremiumGrant($user, $validated['package_type'], (int) $request->user()->id);
            $granted++;
        }

        if ($granted === 0) {
            return back()->withErrors(['user_ids' => 'Premium yalnızca erkek kullanıcılara verilebilir.']);
        }

        $pkg = PremiumSubscription::PACKAGES[$validated['package_type']];
        app(AdminAuditService::class)->log(
            'premium.grant',
            "{$granted} kullanıcıya {$pkg['name']} verildi",
            'premium',
            null,
            ['package' => $validated['package_type'], 'user_ids' => $validated['user_ids']],
        );
        $this->forgetDashboardCache();
        $message = "{$granted} kullanıcıya {$pkg['name']} paketi eklendi ({$pkg['duration_days']} gün).";
        if ($skipped > 0) {
            $message .= " {$skipped} kadın kullanıcı atlandı.";
        }

        return redirect()->route('admin.users', $request->only(['search', 'gender', 'status']))
            ->with('success', $message);
    }

    public function cancelPremium(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $users = User::query()
            ->where('role', 'user')
            ->whereIn('id', $validated['user_ids'])
            ->get();

        $cancelled = 0;
        foreach ($users as $user) {
            $active = $user->premiumSubscriptions()->active()->get();
            foreach ($active as $sub) {
                $sub->update([
                    'is_active' => false,
                    'expires_at' => now(),
                    'payment_reference' => trim(($sub->payment_reference ?? '').';admin-cancel:'.$request->user()->id, ';'),
                ]);
                $cancelled++;
            }
        }

        app(AdminAuditService::class)->log(
            'premium.cancel',
            "{$cancelled} aktif premium iptal edildi",
            'premium',
            null,
            ['user_ids' => $validated['user_ids']],
        );
        $this->forgetDashboardCache();

        return redirect()->route('admin.users', $request->only(['search', 'gender', 'status']))
            ->with('success', $cancelled > 0
                ? "{$cancelled} premium abonelik iptal edildi."
                : 'Seçilen kullanıcılarda aktif premium bulunamadı.');
    }

    public function cancelPremiumSubscription(Request $request, PremiumSubscription $subscription)
    {
        $subscription->update([
            'is_active' => false,
            'expires_at' => now(),
            'payment_reference' => trim(($subscription->payment_reference ?? '').';admin-cancel:'.$request->user()->id, ';'),
        ]);

        app(AdminAuditService::class)->log(
            'premium.cancel',
            'Premium #'.$subscription->id.' iptal edildi',
            'premium',
            (int) $subscription->id,
            ['user_id' => $subscription->user_id],
        );
        $this->forgetDashboardCache();

        return back()->with('success', 'Premium abonelik iptal edildi.');
    }

    private function applyPremiumGrant(User $user, string $packageType, int $adminId): void
    {
        $pkg = PremiumSubscription::PACKAGES[$packageType];
        $active = $user->premiumSubscriptions()->active()->latest('expires_at')->first();

        if ($active) {
            $active->update([
                'package_type' => $packageType,
                'duration_days' => $active->duration_days + $pkg['duration_days'],
                'expires_at' => $active->expires_at->copy()->addDays($pkg['duration_days']),
                'payment_reference' => trim(($active->payment_reference ?? '').';admin-ext:'.$adminId, ';'),
            ]);

            return;
        }

        PremiumSubscription::create([
            'user_id' => $user->id,
            'package_type' => $packageType,
            'price_tl' => 0,
            'duration_days' => $pkg['duration_days'],
            'starts_at' => now(),
            'expires_at' => now()->addDays($pkg['duration_days']),
            'payment_reference' => 'admin-grant:'.$adminId.':'.Str::uuid(),
            'is_active' => true,
        ]);
    }

    public function sendBroadcast(Request $request)
    {
        app(AdminAuditService::class)->ensureBroadcastColumns();

        $request->validate([
            'title' => 'required|string|max:255',
            'message_text' => 'required|string',
            'target_gender' => 'required|in:all,male,female',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $query = User::where('role', 'user');
        if ($request->target_gender !== 'all') {
            $query->where('gender', $request->target_gender);
        }

        $scheduledAt = $request->filled('scheduled_at')
            ? \Illuminate\Support\Carbon::parse($request->scheduled_at)
            : null;
        $isScheduled = $scheduledAt !== null;

        $payload = [
            'admin_id' => $request->user()->id,
            'title' => $request->title,
            'message_text' => $request->message_text,
            'target_gender' => $request->target_gender,
            'sent_count' => $isScheduled ? 0 : $query->count(),
            'created_at' => now(),
        ];

        if (Schema::hasColumn('admin_broadcasts', 'status')) {
            $payload['status'] = $isScheduled ? 'scheduled' : 'sent';
        }
        if (Schema::hasColumn('admin_broadcasts', 'scheduled_at')) {
            $payload['scheduled_at'] = $scheduledAt;
        }

        $broadcast = AdminBroadcast::create($payload);

        app(AdminAuditService::class)->log(
            $isScheduled ? 'broadcast.schedule' : 'broadcast.send',
            $broadcast->title,
            'broadcast',
            (int) $broadcast->id,
            ['target' => $request->target_gender, 'scheduled_at' => optional($scheduledAt)?->toDateTimeString()],
        );

        if ($isScheduled) {
            return redirect()->route('admin.broadcasts')
                ->with('success', 'Duyuru zamanlandı: '.$scheduledAt->format('d.m.Y H:i'));
        }

        $fcm = app(FcmPushService::class);

        if ($fcm->isConfigured()) {
            SendBroadcastPushJob::dispatchAfterResponse($broadcast->id);
            $message = 'Duyuru kaydedildi. Push bildirimleri arka planda iletiliyor.';
        } else {
            $message = 'Duyuru gönderildi. (FCM yapılandırması eksik — yalnızca uygulama içi duyuru.)';
        }

        return redirect()->route('admin.broadcasts')->with('success', $message);
    }

    private function dispatchDueBroadcasts(): void
    {
        if (! Schema::hasTable('admin_broadcasts') || ! Schema::hasColumn('admin_broadcasts', 'status')) {
            return;
        }

        $due = AdminBroadcast::query()->dueScheduled()->limit(20)->get();
        $fcm = app(FcmPushService::class);

        foreach ($due as $broadcast) {
            $query = User::where('role', 'user');
            if ($broadcast->target_gender !== 'all') {
                $query->where('gender', $broadcast->target_gender);
            }

            $broadcast->update([
                'status' => 'sent',
                'sent_count' => $query->count(),
                'created_at' => now(),
            ]);

            if ($fcm->isConfigured()) {
                SendBroadcastPushJob::dispatchAfterResponse($broadcast->id);
            }

            app(AdminAuditService::class)->log(
                'broadcast.send.scheduled',
                $broadcast->title.' zamanı geldi, gönderildi',
                'broadcast',
                (int) $broadcast->id,
            );
        }
    }

    public function updateReport(Request $request, Report $report, NotificationService $notifications)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:2000',
            'ban_reported' => 'nullable|boolean',
        ]);

        $previousNotes = trim((string) ($report->admin_notes ?? ''));
        $previousStatus = (string) $report->status;

        $report->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        $notifications->notifyReportReviewed(
            $report->fresh(),
            $request->user(),
            $previousNotes,
            $previousStatus,
        );

        if ($request->boolean('ban_reported') && $report->reported && $report->reported->role === 'user') {
            $report->reported->update([
                'is_banned' => true,
                'banned_at' => now(),
                'banned_reason' => 'Şikayet sonucu: '.Str::limit($report->reason, 200),
            ]);
            app(AdminAuditService::class)->log(
                'user.ban',
                $report->reported->username.' şikayet sonucu banlandı',
                'user',
                (int) $report->reported->id,
                ['report_id' => $report->id],
            );
        }

        app(AdminAuditService::class)->log(
            'report.update',
            'Şikayet #'.$report->id.' → '.$request->status,
            'report',
            (int) $report->id,
        );

        $this->forgetDashboardCache();

        return redirect()->route('admin.reports')->with('success', 'Şikayet güncellendi.');
    }

    private function forgetDashboardCache(): void
    {
        Cache::forget('admin_dashboard_stats');
        Cache::forget('admin_dashboard_charts');
        Cache::forget('admin_dashboard_full');
        Cache::forget('admin_dashboard_online');
    }
}
