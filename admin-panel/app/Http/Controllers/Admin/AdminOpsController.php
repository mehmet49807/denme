<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AdminUserNote;
use App\Models\AiModerationFlag;
use App\Models\Report;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AdminAuditService;
use App\Services\ContentPolicyService;
use App\Services\FcmPushService;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOpsController extends Controller
{
    public function __construct(private AdminAuditService $audit)
    {
        $this->audit->ensureTables();
        $this->audit->ensureBroadcastColumns();
        $this->audit->ensureStaffRoleColumn();
    }

    public function moderationQueue(Request $request): View
    {
        $pendingReports = Report::with(['reporter', 'reported'])
            ->where('status', 'pending')
            ->latest()
            ->limit(25)
            ->get();

        $aiFlags = Schema::hasTable('ai_moderation_flags')
            ? AiModerationFlag::with('user')
                ->where('status', AiModerationFlag::STATUS_PENDING)
                ->latest()
                ->limit(25)
                ->get()
            : collect();

        $pendingProfiles = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->whereNull('profile_verified_at')
            ->whereNotNull('profile_photo_url')
            ->where('profile_photo_url', '!=', '')
            ->latest()
            ->limit(20)
            ->get();

        $gallerySamples = User::query()
            ->where('role', 'user')
            ->whereNotNull('gallery_photos')
            ->latest()
            ->limit(40)
            ->get()
            ->flatMap(function (User $user) {
                $photos = is_array($user->gallery_photos) ? $user->gallery_photos : [];

                return collect($photos)->take(2)->map(fn ($url, $index) => [
                    'user' => $user,
                    'url' => $url,
                    'index' => $index,
                ]);
            })
            ->take(24)
            ->values();

        $openSupport = Schema::hasTable('support_tickets')
            ? SupportTicket::query()->whereIn('status', ['open', 'pending'])->count()
            : 0;

        return view('admin.moderation-queue', [
            'pendingReports' => $pendingReports,
            'aiFlags' => $aiFlags,
            'pendingProfiles' => $pendingProfiles,
            'gallerySamples' => $gallerySamples,
            'counts' => [
                'reports' => Report::where('status', 'pending')->count(),
                'ai_flags' => Schema::hasTable('ai_moderation_flags')
                    ? AiModerationFlag::where('status', AiModerationFlag::STATUS_PENDING)->count()
                    : 0,
                'profiles' => User::where('role', 'user')->whereNull('profile_verified_at')
                    ->whereNotNull('profile_photo_url')->where('profile_photo_url', '!=', '')->count(),
                'support' => $openSupport,
            ],
        ]);
    }

    public function gallery(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));

        $query = User::query()
            ->where('role', 'user')
            ->whereNotNull('gallery_photos');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(24)->withQueryString();

        $items = $users->getCollection()->flatMap(function (User $user) {
            $photos = is_array($user->gallery_photos) ? $user->gallery_photos : [];

            return collect($photos)->values()->map(fn ($url, $index) => [
                'user' => $user,
                'url' => $url,
                'index' => $index,
            ]);
        })->values();

        return view('admin.gallery-moderation', [
            'users' => $users,
            'items' => $items,
            'search' => $search,
        ]);
    }

    public function removeGalleryPhoto(Request $request, User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        $validated = $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $photos = is_array($user->gallery_photos) ? array_values($user->gallery_photos) : [];
        $index = (int) $validated['index'];

        if (! array_key_exists($index, $photos)) {
            return back()->withErrors(['gallery' => 'Fotoğraf bulunamadı.']);
        }

        $removed = $photos[$index];
        unset($photos[$index]);
        $user->update(['gallery_photos' => array_values($photos)]);

        $this->audit->log(
            'gallery.remove',
            $user->username.' galeri fotoğrafı silindi',
            'user',
            (int) $user->id,
            ['url' => $removed, 'index' => $index],
        );

        return back()->with('success', 'Galeri fotoğrafı kaldırıldı.');
    }

    public function auditLogs(Request $request): View
    {
        $this->audit->ensureTables();

        $query = AdminAuditLog::query()->with('admin')->latest('created_at');

        if ($action = trim((string) $request->get('action', ''))) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where('summary', 'like', "%{$search}%");
        }

        return view('admin.audit-logs', [
            'logs' => Schema::hasTable('admin_audit_logs')
                ? $query->paginate(40)->withQueryString()
                : collect(),
            'ready' => Schema::hasTable('admin_audit_logs'),
        ]);
    }

    public function storeUserNote(Request $request, User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        $this->audit->ensureTables();

        $validated = $request->validate([
            'note' => 'required|string|max:2000',
            'is_pinned' => 'nullable|boolean',
        ]);

        AdminUserNote::query()->create([
            'user_id' => $user->id,
            'admin_id' => $request->user()->id,
            'note' => $validated['note'],
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        $this->audit->log(
            'user.note',
            $user->username.' için not eklendi',
            'user',
            (int) $user->id,
        );

        return back()->with('success', 'Kullanıcı notu kaydedildi.');
    }

    public function destroyUserNote(AdminUserNote $note)
    {
        $userId = (int) $note->user_id;
        $note->delete();

        $this->audit->log('user.note.delete', 'Kullanıcı notu silindi', 'user', $userId);

        return back()->with('success', 'Not silindi.');
    }

    public function autoRules(SiteSettingsService $settings): View
    {
        $policy = app(ContentPolicyService::class);

        return view('admin.auto-rules', [
            'categories' => $policy->configurableCategories(),
            'enabled' => $policy->enabledCategories($settings),
            'customPatterns' => (string) $settings->get('content_policy_custom_patterns', ''),
        ]);
    }

    public function updateAutoRules(Request $request, SiteSettingsService $settings)
    {
        $policy = app(ContentPolicyService::class);
        $categories = array_keys($policy->configurableCategories());

        $payload = [];
        foreach ($categories as $category) {
            $payload['content_policy_'.$category] = $request->boolean($category) ? '1' : '0';
        }

        $payload['content_policy_custom_patterns'] = trim((string) $request->input('custom_patterns', ''));
        $settings->setMany($payload);

        $this->audit->log('policy.update', 'Otomatik içerik kuralları güncellendi', 'settings', null, $payload);

        return back()->with('success', 'Otomatik kurallar kaydedildi.');
    }

    public function systemHealth(): View
    {
        $fcm = app(FcmPushService::class);
        $checks = [];

        try {
            DB::connection()->getPdo();
            $checks['database'] = ['ok' => true, 'label' => 'Veritabanı', 'detail' => 'Bağlantı aktif'];
        } catch (\Throwable $e) {
            $checks['database'] = ['ok' => false, 'label' => 'Veritabanı', 'detail' => 'Bağlantı hatası'];
        }

        try {
            Cache::put('admin_health_ping', now()->timestamp, 30);
            $ok = Cache::get('admin_health_ping') !== null;
            $checks['cache'] = ['ok' => $ok, 'label' => 'Önbellek', 'detail' => $ok ? 'Yazma/okuma OK' : 'Yanıt yok'];
        } catch (\Throwable) {
            $checks['cache'] = ['ok' => false, 'label' => 'Önbellek', 'detail' => 'Erişilemedi'];
        }

        $checks['fcm'] = [
            'ok' => $fcm->isConfigured(),
            'label' => 'FCM Push',
            'detail' => $fcm->isConfigured()
                ? $fcm->registeredDeviceCount().' kayıtlı cihaz'
                : 'Yapılandırma eksik',
        ];

        $pendingReports = Report::where('status', 'pending')->count();
        $pendingAi = Schema::hasTable('ai_moderation_flags')
            ? AiModerationFlag::where('status', AiModerationFlag::STATUS_PENDING)->count()
            : 0;
        $pendingProfiles = User::where('role', 'user')->whereNull('profile_verified_at')
            ->whereNotNull('profile_photo_url')->where('profile_photo_url', '!=', '')->count();
        $openSupport = Schema::hasTable('support_tickets')
            ? SupportTicket::query()->whereIn('status', ['open', 'pending'])->count()
            : 0;

        $checks['moderation'] = [
            'ok' => ($pendingReports + $pendingAi) < 50,
            'label' => 'Denetim yükü',
            'detail' => "{$pendingReports} şikayet · {$pendingAi} AI bayrak · {$pendingProfiles} profil · {$openSupport} destek",
        ];

        $diskFree = @disk_free_space(base_path());
        $diskTotal = @disk_total_space(base_path());
        $diskOk = $diskFree === false || $diskTotal === false
            ? true
            : ($diskFree / max($diskTotal, 1)) > 0.08;
        $checks['disk'] = [
            'ok' => $diskOk,
            'label' => 'Disk alanı',
            'detail' => ($diskFree && $diskTotal)
                ? round($diskFree / 1048576).' MB boş / '.round($diskTotal / 1048576).' MB'
                : 'Ölçülemedi',
        ];

        return view('admin.system-health', [
            'checks' => $checks,
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'appEnv' => config('app.env'),
        ]);
    }

    public function staffRoles(): View
    {
        $this->audit->ensureStaffRoleColumn();

        $staff = User::query()
            ->whereIn('role', ['admin', 'moderator', 'support'])
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'moderator' THEN 2 WHEN 'support' THEN 3 ELSE 4 END")
            ->orderBy('username')
            ->get();

        return view('admin.staff-roles', [
            'staff' => $staff,
            'roleLabels' => [
                'admin' => 'Yönetici',
                'moderator' => 'Moderatör',
                'support' => 'Destek',
            ],
        ]);
    }

    public function updateStaffRole(Request $request, User $user)
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $this->audit->ensureStaffRoleColumn();

        $validated = $request->validate([
            'role' => 'required|in:admin,moderator,support,user',
        ]);

        if ((int) $user->id === (int) $request->user()->id && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Kendi yönetici rolünüzü düşüremezsiniz.']);
        }

        $previous = $user->role;

        try {
            $user->update(['role' => $validated['role']]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['role' => 'Rol güncellenemedi. Veritabanı role kolonunu kontrol edin.']);
        }

        $this->audit->log(
            'staff.role',
            $user->username.' rolü '.$previous.' → '.$validated['role'],
            'user',
            (int) $user->id,
            ['from' => $previous, 'to' => $validated['role']],
        );

        return back()->with('success', 'Rol güncellendi.');
    }

    public function promoteStaff(Request $request)
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $this->audit->ensureStaffRoleColumn();

        $validated = $request->validate([
            'username' => 'required|string|max:50',
            'role' => 'required|in:admin,moderator,support',
        ]);

        $username = trim($validated['username']);
        $user = User::query()
            ->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])
            ->first();

        if (! $user) {
            return back()->withErrors(['username' => 'Kullanıcı bulunamadı: '.$username])->withInput();
        }

        $previous = $user->role;

        try {
            $user->update(['role' => $validated['role']]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['username' => 'Rol atanamadı. Lütfen tekrar deneyin.'])->withInput();
        }

        $this->audit->log(
            'staff.promote',
            $user->username.' '.$previous.' → '.$validated['role'],
            'user',
            (int) $user->id,
        );

        return back()->with('success', $user->username.' personel olarak eklendi ('.$validated['role'].').');
    }

    public function exportUsersCsv(): StreamedResponse
    {
        $filename = 'users-export-'.now()->format('Ymd-His').'.csv';

        $this->audit->log('backup.users_csv', 'Kullanıcı CSV dışa aktarıldı');

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'username', 'email', 'gender', 'city', 'is_banned', 'role', 'created_at']);

            User::query()
                ->where('role', 'user')
                ->orderBy('id')
                ->chunk(500, function ($users) use ($out) {
                    foreach ($users as $user) {
                        fputcsv($out, [
                            $user->id,
                            $user->username,
                            $user->email,
                            $user->gender,
                            $user->city,
                            $user->is_banned ? 1 : 0,
                            $user->role,
                            optional($user->created_at)?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSettingsJson(SiteSettingsService $settings): \Illuminate\Http\JsonResponse
    {
        $this->audit->log('backup.settings_json', 'Site ayarları dışa aktarıldı');

        return response()->json([
            'exported_at' => now()->toIso8601String(),
            'settings' => $settings->all(),
        ], 200, [
            'Content-Disposition' => 'attachment; filename="site-settings-'.now()->format('Ymd-His').'.json"',
        ]);
    }
}
