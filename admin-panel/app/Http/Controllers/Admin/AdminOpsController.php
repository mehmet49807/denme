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

        $fcmStatus = $fcm->status();
        $checks['fcm'] = [
            'ok' => $fcmStatus['configured'],
            'label' => 'FCM Push',
            'detail' => $fcmStatus['configured']
                ? $fcmStatus['device_count'].' kayıtlı cihaz · '.$fcmStatus['project_id']
                : 'Yapılandırma eksik — service account JSON yükleyin',
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
            'fcmStatus' => $fcmStatus,
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'appEnv' => config('app.env'),
        ]);
    }

    public function uploadFcmCredentials(Request $request, FcmPushService $fcm)
    {
        $request->validate([
            'credentials' => 'nullable|file|max:512',
            'json' => 'nullable|string|max:200000',
        ]);

        $json = trim((string) $request->input('json', ''));
        if ($json === '' && $request->hasFile('credentials')) {
            $json = (string) file_get_contents($request->file('credentials')->getRealPath());
        }

        if ($json === '') {
            return back()->with('error', 'Service account JSON dosyası seçin veya JSON yapıştırın.');
        }

        $result = $fcm->installCredentialsJson($json);

        if (! ($result['ok'] ?? false)) {
            return back()->with('error', $result['error'] ?? 'FCM kimlik bilgileri yüklenemedi.');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Throwable) {
            //
        }

        $this->audit->log(
            'fcm.credentials_upload',
            'Firebase service account JSON yüklendi',
            'settings',
            null,
            ['mirrored' => count($result['mirrored'] ?? [])]
        );

        return back()->with(
            'success',
            'FCM yapılandırması tamam · '.$fcm->registeredDeviceCount().' kayıtlı cihaz'
        );
    }

    public function staffRoles(Request $request): View
    {
        $this->audit->ensureStaffRoleColumn();

        $roleLabels = [
            'admin' => 'Yönetici',
            'moderator' => 'Moderatör',
            'support' => 'Destek',
            'user' => 'Üye',
        ];

        $staff = User::query()
            ->whereIn('role', ['admin', 'moderator', 'support'])
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'moderator' THEN 2 WHEN 'support' THEN 3 ELSE 4 END")
            ->orderBy('username')
            ->get();

        $search = trim((string) $request->get('q', ''));
        $searchResults = collect();

        if ($search !== '') {
            $searchResults = User::query()
                ->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orderBy('username')
                ->limit(20)
                ->get(['id', 'username', 'email', 'role', 'first_name', 'last_name']);
        }

        return view('admin.staff-roles', [
            'staff' => $staff,
            'search' => $search,
            'searchResults' => $searchResults,
            'roleLabels' => $roleLabels,
            'canManage' => (bool) $request->user()?->isAdmin(),
        ]);
    }

    public function updateStaffRole(Request $request, User $user)
    {
        return $this->assignStaffRole($request, $user, (string) $request->input('role', ''));
    }

    public function promoteStaff(Request $request)
    {
        $this->audit->ensureStaffRoleColumn();

        if (! $request->user()?->isAdmin()) {
            return back()->withErrors(['role' => 'Rol değiştirmek için yönetici olmalısınız.']);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'username' => 'nullable|string|max:50',
            'role' => 'required|in:admin,moderator,support,user',
        ]);

        $user = null;
        if (! empty($validated['user_id'])) {
            $user = User::query()->find($validated['user_id']);
        } elseif (! empty($validated['username'])) {
            $username = trim($validated['username']);
            $user = User::query()
                ->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])
                ->first();
            if (! $user) {
                return back()->withErrors(['username' => 'Kullanıcı bulunamadı: '.$username])->withInput();
            }
        } else {
            return back()->withErrors(['username' => 'Kullanıcı seçin veya kullanıcı adı yazın.'])->withInput();
        }

        return $this->assignStaffRole($request, $user, $validated['role']);
    }

    private function assignStaffRole(Request $request, User $user, string $role)
    {
        if (! $request->user()?->isAdmin()) {
            return back()->withErrors(['role' => 'Rol değiştirmek için yönetici olmalısınız.']);
        }

        $this->audit->ensureStaffRoleColumn();

        if (! in_array($role, ['admin', 'moderator', 'support', 'user'], true)) {
            return back()->withErrors(['role' => 'Geçersiz rol.']);
        }

        if ((int) $user->id === (int) $request->user()->id && $role !== 'admin') {
            return back()->withErrors(['role' => 'Kendi yönetici rolünüzü düşüremezsiniz.']);
        }

        $previous = $user->role;
        $labels = [
            'admin' => 'Yönetici',
            'moderator' => 'Moderatör',
            'support' => 'Destek',
            'user' => 'Üye',
        ];

        try {
            $user->update(['role' => $role]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['role' => 'Rol kaydedilemedi. Sayfayı yenileyip tekrar deneyin.']);
        }

        $this->audit->log(
            'staff.role',
            $user->username.' rolü '.$previous.' → '.$role,
            'user',
            (int) $user->id,
            ['from' => $previous, 'to' => $role],
        );

        $label = $labels[$role] ?? $role;

        return redirect()
            ->route('admin.staff', ['q' => $user->username])
            ->with('success', $user->username.' → '.$label);
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
