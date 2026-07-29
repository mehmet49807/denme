<?php

declare(strict_types=1);

require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/Database.php';
require __DIR__ . '/app/Auth.php';
require __DIR__ . '/app/CustomerAuth.php';
require __DIR__ . '/app/DiscountService.php';
require __DIR__ . '/app/SiteContent.php';
require __DIR__ . '/app/BrochureService.php';
require __DIR__ . '/app/OrderService.php';
require __DIR__ . '/app/TableService.php';
require __DIR__ . '/app/FiscalService.php';
require __DIR__ . '/app/FranchiseService.php';
require __DIR__ . '/app/CategorySync.php';
require __DIR__ . '/app/SchemaSync.php';
require __DIR__ . '/app/MenuImageSync.php';
require __DIR__ . '/app/MenuItemSync.php';
require __DIR__ . '/app/Router.php';

$config = config();
date_default_timezone_set((string) ($config['timezone'] ?? 'Europe/Istanbul'));
session_name((string) ($config['session_name'] ?? 'chicken_session'));
session_start();

$path = current_path();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Allow installer even if DB is missing
if ($path === '/install') {
    redirect('/install.php');
}
if ($path === '/install.php') {
    require __DIR__ . '/install.php';
    exit;
}

try {
    $installed = Database::isInstalled();
} catch (Throwable) {
    $installed = false;
}

if (!$installed && $path !== '/install.php') {
    redirect('/install.php');
}

if ($installed) {
    SchemaSync::ensure();
    CategorySync::ensure();
    MenuItemSync::ensure();
    MenuImageSync::ensure();
    // Gece 00:00 sonrası eksik gün sonlarını otomatik kapat (lazy + cron).
    if (
        $path === '/cron/gun-sonu'
        || str_starts_with($path, '/kasa')
        || str_starts_with($path, '/yonetici')
    ) {
        try {
            FiscalService::ensureAutoDayCloses();
        } catch (Throwable $e) {
            error_log('ensureAutoDayCloses: ' . $e->getMessage());
        }
    }
}

$router = new Router();

// Sunucu cron: her gece 00:05 civarı GET /cron/gun-sonu (auth yok, idempotent)
$router->get('/cron/gun-sonu', static function (): void {
    $closed = FiscalService::ensureAutoDayCloses();
    json_response([
        'ok' => true,
        'message' => 'Gün sonu raporları güncellendi',
        'closed_dates' => $closed,
    ]);
});

$menuCatalog = static function (): array {
    $pdo = Database::pdo();
    $categories = $pdo->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    $items = $pdo->query(
        'SELECT m.*, c.name AS category_name, c.slug AS category_slug
         FROM menu_items m
         JOIN categories c ON c.id = m.category_id
         WHERE m.is_available = 1
         ORDER BY c.sort_order, m.sort_order, m.id'
    )->fetchAll();
    return compact('categories', 'items');
};

$router->get('/', static function () use ($menuCatalog): void {
    $catalog = $menuCatalog();
    view('public/home', [
        'title' => 'Chicken — Izgara Tavuk',
        'categories' => $catalog['categories'],
        'items' => $catalog['items'],
    ]);
});

$router->get('/menu', static function () use ($menuCatalog): void {
    // Eski masa QR'ları broşüre yönlendirilir
    $token = trim((string) input('t', ''));
    if ($token !== '') {
        redirect('/menu/brosur?t=' . rawurlencode($token));
    }
    $catalog = $menuCatalog();
    view('public/menu', [
        'title' => 'Menü',
        'categories' => $catalog['categories'],
        'items' => $catalog['items'],
        'table' => null,
    ]);
});

$router->get('/menu/brosur', static function () use ($menuCatalog): void {
    $catalog = $menuCatalog();
    $table = null;
    $token = trim((string) input('t', ''));
    if ($token !== '') {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM dining_tables WHERE qr_token = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$token]);
        $table = $stmt->fetch() ?: null;
    }

    $preview = trim((string) input('preview', ''));
    $themeId = BrochureService::selectedThemeId();
    if ($preview !== '' && Auth::check() && Auth::role() === 'admin') {
        $catalogThemes = BrochureService::catalog();
        if (isset($catalogThemes[$preview])) {
            $themeId = $preview;
        }
    }

    view('public/menu_brochure', [
        'title' => 'Chicken Menü Broşürü',
        'categories' => $catalog['categories'],
        'items' => $catalog['items'],
        'table' => $table,
        'themeId' => $themeId,
        'layout' => 'brochure',
    ]);
});

$router->get('/siparis', static function () use ($menuCatalog): void {
    $catalog = $menuCatalog();
    if (CustomerAuth::check()) {
        CustomerAuth::refresh();
    }
    view('public/order', [
        'title' => 'Online Sipariş',
        'categories' => $catalog['categories'],
        'items' => $catalog['items'],
        'customer' => CustomerAuth::user(),
        'welcomeCode' => DiscountService::WELCOME_CODE,
    ]);
});

$router->post('/api/orders', static function (): void {
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($payload)) {
        json_response(['ok' => false, 'error' => 'Geçersiz istek'], 400);
    }
    try {
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $customerPhone = trim((string) ($payload['customer_phone'] ?? ''));
        if ($customerName === '' || $customerPhone === '') {
            json_response(['ok' => false, 'error' => 'Ad ve telefon zorunlu.'], 422);
        }
        $customer = CustomerAuth::user();
        if ($customer) {
            CustomerAuth::refresh();
            $customer = CustomerAuth::user();
        }
        $order = OrderService::create([
            'source' => 'online',
            'customer_id' => $customer ? (int) $customer['id'] : null,
            'customer' => $customer,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_note' => trim((string) ($payload['customer_note'] ?? '')),
            'payment_preference' => trim((string) ($payload['payment_preference'] ?? '')),
            'discount_code' => trim((string) ($payload['discount_code'] ?? '')),
            'items' => $payload['items'] ?? [],
        ]);
        json_response(['ok' => true, 'order' => [
            'id' => (int) $order['id'],
            'order_code' => $order['order_code'],
            'status' => $order['status'],
            'total' => (float) $order['total'],
            'discount_amount' => (float) ($order['discount_amount'] ?? 0),
            'payment_preference' => $order['payment_preference'] ?? null,
        ]]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

function staff_home_path(?string $role): string
{
    return match ($role) {
        'admin' => '/yonetici',
        'cashier' => '/kasa',
        'waiter' => '/garson',
        default => '/garson',
    };
}

$router->get('/giris', static function (): void {
    if (Auth::check()) {
        redirect(staff_home_path(Auth::role()));
    }
    if (CustomerAuth::check()) {
        redirect('/siparis');
    }
    view('public/login', ['title' => 'Giriş']);
});

$router->post('/giris', static function (): void {
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'Oturum doğrulaması başarısız.');
        redirect('/giris');
    }
    $login = trim((string) input('login'));
    $password = (string) input('password');

    // Staff first: garson / kasa / yönetici → own panel
    if (Auth::attempt($login, $password)) {
        CustomerAuth::logout();
        redirect(staff_home_path(Auth::role()));
    }

    if (CustomerAuth::attempt($login, $password)) {
        redirect('/siparis');
    }

    flash('error', 'E-posta / kullanıcı adı veya parola hatalı.');
    redirect('/giris');
});

$router->get('/uye-ol', static function (): void {
    if (Auth::check()) {
        redirect(staff_home_path(Auth::role()));
    }
    if (CustomerAuth::check()) {
        redirect('/siparis');
    }
    view('public/register', [
        'title' => 'Üye ol',
        'welcomeCode' => DiscountService::WELCOME_CODE,
        'welcomePercent' => (int) DiscountService::WELCOME_PERCENT,
    ]);
});

$router->post('/uye-ol', static function (): void {
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'Oturum doğrulaması başarısız.');
        redirect('/uye-ol');
    }
    if (
        (string) input('accept_terms') !== '1'
        || (string) input('accept_kvkk') !== '1'
        || (string) input('accept_distance') !== '1'
    ) {
        flash('error', 'Devam etmek için zorunlu sözleşmeleri onaylayın.');
        redirect('/uye-ol');
    }
    try {
        CustomerAuth::register(
            (string) input('name'),
            (string) input('email'),
            (string) input('phone'),
            (string) input('password'),
            (string) input('address')
        );
        Auth::logout();
        flash('success', 'Hoş geldiniz! İlk siparişinizde ' . DiscountService::WELCOME_CODE . ' kodu ile %10 indirim.');
        redirect('/siparis');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect('/uye-ol');
    }
});

$contentPage = static function (string $slug): void {
    $page = SiteContent::page($slug);
    if (!$page) {
        http_response_code(404);
        view('errors/404', ['title' => 'Sayfa bulunamadı']);
        return;
    }
    view('public/content', [
        'title' => $page['title'],
        'eyebrow' => $page['eyebrow'],
        'heading' => $page['heading'],
        'sections' => $page['sections'],
    ]);
};

$router->get('/hakkimizda', static function () use ($contentPage): void {
    $contentPage('hakkimizda');
});
$router->get('/misyon', static function () use ($contentPage): void {
    $contentPage('misyon');
});
$router->get('/musteri-memnuniyeti', static function () use ($contentPage): void {
    $contentPage('musteri-memnuniyeti');
});

$router->get('/bayilik', static function (): void {
    view('public/franchise', [
        'title' => 'Franchise · Crisp & Co.',
    ]);
});

$router->post('/bayilik', static function (): void {
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'Oturum doğrulaması başarısız. Tekrar deneyin.');
        redirect('/bayilik#basvuru');
    }
    try {
        FranchiseService::create([
            'name' => (string) input('name'),
            'phone' => (string) input('phone'),
            'email' => (string) input('email'),
            'city' => (string) input('city'),
            'district' => (string) input('district'),
            'budget' => (string) input('budget'),
            'experience' => (string) input('experience'),
            'message' => (string) input('message'),
            'accept_terms' => input('accept_terms'),
            'accept_kvkk' => input('accept_kvkk'),
        ]);
        flash('success', 'Başvurunuz alındı. Ekibimiz en kısa sürede sizinle iletişime geçecek.');
        redirect('/bayilik#basvuru');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        view('public/franchise', [
            'title' => 'Franchise · Crisp & Co.',
        ]);
    }
});

$router->get('/sozlesmeler/uyelik', static function () use ($contentPage): void {
    $contentPage('uyelik');
});
$router->get('/sozlesmeler/kullanim', static function () use ($contentPage): void {
    $contentPage('kullanim');
});
$router->get('/sozlesmeler/kvkk', static function () use ($contentPage): void {
    $contentPage('kvkk');
});
$router->get('/sozlesmeler/acik-riza', static function () use ($contentPage): void {
    $contentPage('acik-riza');
});
$router->get('/sozlesmeler/gizlilik', static function () use ($contentPage): void {
    $contentPage('gizlilik');
});
$router->get('/sozlesmeler/mesafeli-satis', static function () use ($contentPage): void {
    $contentPage('mesafeli-satis');
});

$router->post('/cikis', static function (): void {
    CustomerAuth::logout();
    redirect('/');
});

$router->get('/takip', static function (): void {
    $code = trim((string) input('code', ''));
    $order = $code !== '' ? OrderService::findByCode($code) : null;
    view('public/track', [
        'title' => 'Sipariş Takip',
        'code' => $code,
        'order' => $order,
    ]);
});

$router->get('/api/orders/{code}', static function (string $code): void {
    $order = OrderService::findByCode($code);
    if (!$order) {
        json_response(['ok' => false, 'error' => 'Sipariş bulunamadı'], 404);
    }
    json_response(['ok' => true, 'order' => [
        'order_code' => $order['order_code'],
        'status' => $order['status'],
        'status_label' => status_label($order['status']),
        'source' => $order['source'],
        'total' => (float) $order['total'],
        'created_at' => $order['created_at'],
        'items' => $order['items'],
        'events' => $order['events'],
    ]]);
});

// Menü broşürü QR (ortasında logo) — /qr altına koyma (FTP'de qr/ klasörü /qr sayfasını bozabilir)
$router->get('/api/qr-brosur', static function (): void {
    try {
        $size = (int) ($_GET['size'] ?? 320);
        BrochureService::outputBrandedQrPng($size);
    } catch (Throwable $e) {
        error_log('qr-brosur: ' . $e->getMessage());
        header('Location: ' . BrochureService::plainQrRemoteUrl(null, 320), true, 302);
    }
    exit;
});

$router->get('/qr', static function (): void {
    Auth::requireRole('cashier', 'admin');
    try {
        $brochureUrl = BrochureService::brochurePublicUrl();
        $qrImageUrl = BrochureService::qrImageUrl($brochureUrl, 320);
        $selectedId = BrochureService::selectedThemeId();
        $selectedName = BrochureService::catalog()[$selectedId]['name'] ?? $selectedId;
        view('staff/qr', [
            'title' => 'QR Menü',
            'brochureUrl' => $brochureUrl,
            'qrImageUrl' => $qrImageUrl,
            'logoUrl' => logo_url(),
            'qrDownloadUrl' => BrochureService::qrBrandedDownloadUrl(480),
            'canEdit' => Auth::role() === 'admin',
            'selectedThemeName' => $selectedName,
            'user' => Auth::user(),
        ]);
    } catch (Throwable $e) {
        error_log('qr page: ' . $e->getMessage());
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="tr"><meta charset="utf-8"><body style="font-family:sans-serif;padding:24px">';
        echo '<h1>QR Menü geçici olarak açılamadı</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p><a href="' . htmlspecialchars(url('/kasa'), ENT_QUOTES, 'UTF-8') . '">Kasaya dön</a></p>';
        echo '</body></html>';
    }
});

$router->get('/yonetici/brosurler', static function (): void {
    Auth::requireRole('admin');
    $brochureUrl = BrochureService::brochurePublicUrl();
    view('staff/admin_brochures', [
        'title' => 'Yönetici · Broşür temaları',
        'themes' => BrochureService::themesForAdmin(),
        'brochureUrl' => $brochureUrl,
        'qrImageUrl' => BrochureService::qrImageUrl($brochureUrl, 220),
        'user' => Auth::user(),
    ]);
});

$router->post('/yonetici/brosurler', static function (): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici/brosurler');
    }
    $action = (string) input('action');
    $themeId = trim((string) input('theme_id'));
    try {
        if ($action === 'select') {
            BrochureService::selectTheme($themeId);
            flash('success', 'Broşür teması seçildi.');
        } elseif ($action === 'toggle') {
            $active = (string) input('active') === '1';
            BrochureService::setThemeActive($themeId, $active);
            flash('success', $active ? 'Tema aktifleştirildi.' : 'Tema pasife alındı.');
        } else {
            flash('error', 'Geçersiz işlem.');
        }
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }
    redirect('/yonetici/brosurler');
});

// Staff auth
$router->get('/personel/giris', static function (): void {
    if (Auth::check()) {
        redirect(staff_home_path(Auth::role()));
    }
    // Müşteri giriş sayfasına yönlendir (personel de oradan girebilir)
    redirect('/giris');
});

$router->post('/personel/giris', static function (): void {
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'Oturum doğrulaması başarısız.');
        redirect('/giris');
    }
    $ok = Auth::attempt((string) input('username'), (string) input('password'));
    if (!$ok) {
        flash('error', 'Kullanıcı adı veya parola hatalı.');
        redirect('/giris');
    }
    CustomerAuth::logout();
    redirect(staff_home_path(Auth::role()));
});

$router->post('/personel/cikis', static function (): void {
    Auth::logout();
    redirect('/giris');
});

// Waiter
$router->get('/garson', static function () use ($menuCatalog): void {
    Auth::requireRole('waiter', 'admin');
    $catalog = $menuCatalog();
    view('staff/waiter', [
        'title' => 'Garson Paneli',
        'categories' => $catalog['categories'],
        'items' => $catalog['items'],
        'user' => Auth::user(),
    ]);
});

$router->get('/siparisler', static function (): void {
    Auth::requireRole('waiter', 'admin');
    $pdo = Database::pdo();
    $tables = OrderService::tablesOverview();
    $allTables = $pdo->query('SELECT * FROM dining_tables WHERE is_active = 1 ORDER BY id')->fetchAll();
    view('staff/orders', [
        'title' => 'Siparişler',
        'tables' => $allTables,
        'openTables' => array_values(array_filter($tables, static fn(array $t): bool => !empty($t['is_open']))),
        'user' => Auth::user(),
        'canManage' => Auth::role() === 'admin',
    ]);
});

$router->get('/masa/ekle', static function (): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $role = Auth::role();
    $canPickOpener = in_array($role, ['admin', 'cashier'], true);
    $back = match ($role) {
        'cashier' => '/kasa',
        'waiter' => '/siparisler',
        default => '/yonetici/masalar',
    };
    $label = match ($role) {
        'cashier' => 'Kasa',
        'waiter' => 'Garson',
        default => 'Yönetici',
    };
    // Kasa yönetici adına masa açamaz; yalnızca garson/kasa seçebilir.
    $openerRoles = $role === 'cashier' ? ['waiter', 'cashier'] : ['waiter', 'cashier', 'admin'];
    view('staff/table_add', [
        'title' => $label . ' · Yeni masa',
        'user' => Auth::user(),
        'staffOptions' => $canPickOpener ? TableService::staffOptions($openerRoles) : [],
        'canPickOpener' => $canPickOpener,
        'backUrl' => url($back),
        'formAction' => url('/masa/ekle'),
        'roleLabel' => $label,
    ]);
});

$router->post('/masa/ekle', static function (): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $role = Auth::role();
    $backForm = '/masa/ekle';
    $successRedirect = match ($role) {
        'cashier' => '/kasa',
        'waiter' => '/siparisler',
        default => '/yonetici/masalar',
    };
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect($backForm);
    }

    $masaNo = trim((string) input('masa_no'));
    $seats = (int) input('seats');

    // Garson yalnızca kendi adıyla masa açabilir; yönetici/kasa seçebilir.
    if ($role === 'waiter') {
        $openedByStaffId = (int) Auth::id();
        $openedByName = (string) (Auth::user()['name'] ?? '');
        if ($openedByStaffId <= 0 || $openedByName === '') {
            flash('error', 'Garson oturumu geçersiz.');
            redirect($backForm);
        }
    } else {
        $openedByStaffId = (int) input('opened_by_staff_id');
        $openedByName = '';
        if ($openedByStaffId > 0) {
            $stmt = Database::pdo()->prepare(
                'SELECT id, name, role FROM staff WHERE id = ? AND is_active = 1 LIMIT 1'
            );
            $stmt->execute([$openedByStaffId]);
            $staff = $stmt->fetch();
            if (!$staff) {
                flash('error', 'Masa açan kişi bulunamadı.');
                redirect($backForm);
            }
            // Kasa, yönetici adına masa açamaz.
            if ($role === 'cashier' && ($staff['role'] ?? '') === 'admin') {
                flash('error', 'Kasa yönetici adına masa açamaz.');
                redirect($backForm);
            }
            $openedByName = (string) $staff['name'];
        } else {
            flash('error', 'Masa açan kişi seçin.');
            redirect($backForm);
        }
    }

    try {
        $table = TableService::create($masaNo, $seats, $openedByStaffId, $openedByName);
        flash('success', $table['label'] . ' eklendi · ' . $table['seats'] . ' kişi · Açan: ' . $openedByName);
        if ($role === 'waiter') {
            redirect('/garson/masa/' . $table['id']);
        }
        if ($role === 'cashier') {
            redirect('/kasa/masa/' . $table['id']);
        }
        redirect($successRedirect);
    } catch (Throwable $e) {
        flash('error', $e->getMessage() !== '' ? $e->getMessage() : 'Masa eklenemedi.');
        redirect($backForm);
    }
});

$router->get('/garson/masa/{id}', static function (string $id) use ($menuCatalog): void {
    Auth::requireRole('waiter', 'admin');
    $table = OrderService::findTable((int) $id);
    if (!$table) {
        http_response_code(404);
        echo 'Masa bulunamadı';
        return;
    }
    $catalog = $menuCatalog();
    $orders = OrderService::openOrdersForTable((int) $id);
    $role = Auth::role();
    $uid = Auth::id();
    view('staff/table_detail', [
        'title' => $table['label'],
        'table' => $table,
        'orders' => $orders,
        'items' => $catalog['items'],
        'categories' => $catalog['categories'],
        'user' => Auth::user(),
        'mode' => 'waiter',
        'canPay' => $role === 'admin',
        'canCancel' => $role === 'admin',
        // Masa kapatma yalnızca yönetici / kasa — garson bu rotada kapatamaz.
        'canClose' => $role === 'admin',
        'canAddToOrder' => static function (array $order) use ($role, $uid): bool {
            if ($role === 'admin') {
                return true;
            }
            return (int) ($order['waiter_id'] ?? 0) === (int) $uid;
        },
        'canEditItemNote' => static function (array $order) use ($role, $uid): bool {
            if ($role === 'admin') {
                return true;
            }
            return (int) ($order['waiter_id'] ?? 0) === (int) $uid;
        },
    ]);
});

$router->post('/api/staff/orders', static function (): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($payload)) {
        json_response(['ok' => false, 'error' => 'Geçersiz istek'], 400);
    }

    $role = Auth::role();
    $targetOrderId = (int) ($payload['order_id'] ?? 0);
    $items = $payload['items'] ?? [];

    try {
        if ($targetOrderId > 0) {
            $order = OrderService::findById($targetOrderId);
            if (!$order) {
                throw new InvalidArgumentException('Sipariş bulunamadı.');
            }
            if ($role === 'waiter' && (int) ($order['waiter_id'] ?? 0) !== (int) Auth::id()) {
                json_response(['ok' => false, 'error' => 'Sadece kendi siparişinize ürün ekleyebilirsiniz.'], 403);
            }
            $order = OrderService::addItems($targetOrderId, $items, Auth::id());
            json_response(['ok' => true, 'order' => $order]);
        }

        $source = $role === 'cashier' ? 'cashier' : 'waiter';
        $order = OrderService::create([
            'source' => $source,
            'table_id' => (int) ($payload['table_id'] ?? 0),
            'waiter_id' => Auth::id(),
            'customer_note' => trim((string) ($payload['customer_note'] ?? '')),
            'items' => $items,
        ]);
        json_response(['ok' => true, 'order' => $order]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

$router->get('/garson/fis/{id}', static function (string $id): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $order = OrderService::findById((int) $id);
    if (!$order) {
        http_response_code(404);
        echo 'Fiş bulunamadı';
        return;
    }
    view('staff/slips', [
        'title' => 'Sipariş Fişleri #' . $order['order_code'],
        'order' => $order,
        'user' => Auth::user(),
    ]);
});

// Station boards
$router->get('/mutfak', static function (): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $pdo = Database::pdo();
    $rows = $pdo->query(
        "SELECT oi.*, o.order_code, o.table_id, t.label AS table_label, o.source, o.customer_note, o.created_at AS order_time
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         LEFT JOIN dining_tables t ON t.id = o.table_id
         WHERE oi.station = 'kitchen'
           AND oi.status IN ('queued','preparing')
           AND o.status NOT IN ('cancelled','paid','pending')
         ORDER BY oi.id ASC"
    )->fetchAll();
    view('staff/station', [
        'title' => 'Mutfak Ekranı',
        'station' => 'kitchen',
        'rows' => $rows,
        'user' => Auth::user(),
    ]);
});

$router->get('/bar', static function (): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $pdo = Database::pdo();
    $rows = $pdo->query(
        "SELECT oi.*, o.order_code, o.table_id, t.label AS table_label, o.source, o.customer_note, o.created_at AS order_time
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         LEFT JOIN dining_tables t ON t.id = o.table_id
         WHERE oi.station = 'bar'
           AND oi.status IN ('queued','preparing')
           AND o.status NOT IN ('cancelled','paid','pending')
         ORDER BY oi.id ASC"
    )->fetchAll();
    view('staff/station', [
        'title' => 'Bar Ekranı',
        'station' => 'bar',
        'rows' => $rows,
        'user' => Auth::user(),
    ]);
});

$router->post('/api/station/item-status', static function (): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    $itemId = (int) ($payload['item_id'] ?? 0);
    $status = (string) ($payload['status'] ?? '');
    if (!in_array($status, ['preparing', 'ready', 'served', 'cancelled'], true)) {
        json_response(['ok' => false, 'error' => 'Geçersiz durum'], 422);
    }
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('UPDATE order_items SET status = ? WHERE id = ?');
    $stmt->execute([$status, $itemId]);
    json_response(['ok' => true]);
});

// Cashier
$router->get('/kasa', static function (): void {
    Auth::requireRole('cashier', 'admin');
    $tables = OrderService::tablesOverview();
    $orders = OrderService::listRecent(['from' => date('Y-m-d 00:00:00')], 150);
    $onlineOpen = array_values(array_filter(
        $orders,
        static fn(array $o): bool => $o['source'] === 'online' && !in_array($o['status'], ['paid', 'cancelled'], true)
    ));
    $pendingOnlineCount = count(array_filter(
        $onlineOpen,
        static fn(array $o): bool => ($o['status'] ?? '') === 'pending'
    ));
    view('staff/cashier', [
        'title' => 'Kasa',
        'tables' => $tables,
        'orders' => $orders,
        'onlineOpen' => $onlineOpen,
        'pendingOnlineCount' => $pendingOnlineCount,
        'user' => Auth::user(),
    ]);
});

$router->get('/online-siparisler', static function (): void {
    Auth::requireRole('cashier', 'admin');
    $pendingRows = OrderService::listOnlinePending(60);
    $pending = [];
    foreach ($pendingRows as $row) {
        $full = OrderService::findById((int) $row['id']);
        if ($full) {
            $pending[] = $full;
        }
    }
    $activeRows = OrderService::listOnlineActive(30);
    $active = [];
    foreach ($activeRows as $row) {
        $full = OrderService::findById((int) $row['id']);
        if ($full) {
            $active[] = $full;
        }
    }
    view('staff/online_orders', [
        'title' => 'Online Siparişler',
        'pending' => $pending,
        'active' => $active,
        'user' => Auth::user(),
    ]);
});

$router->post('/api/online-orders/{id}/accept', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    try {
        $order = OrderService::acceptOnlineOrder((int) $id, Auth::id());
        json_response([
            'ok' => true,
            'order' => [
                'id' => (int) $order['id'],
                'order_code' => $order['order_code'],
                'status' => $order['status'],
                'status_label' => status_label((string) $order['status']),
            ],
        ]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

$router->post('/api/online-orders/{id}/reject', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    try {
        $order = OrderService::findById((int) $id);
        if (!$order || ($order['source'] ?? '') !== 'online') {
            throw new InvalidArgumentException('Online sipariş bulunamadı.');
        }
        OrderService::cancelOrder((int) $id, Auth::id());
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

$router->get('/api/online-orders/pending-count', static function (): void {
    Auth::requireRole('cashier', 'admin');
    $count = count(OrderService::listOnlinePending(200));
    json_response(['ok' => true, 'count' => $count]);
});

$router->get('/kasa/masa/{id}', static function (string $id) use ($menuCatalog): void {
    Auth::requireRole('cashier', 'admin');
    $table = OrderService::findTable((int) $id);
    if (!$table) {
        http_response_code(404);
        echo 'Masa bulunamadı';
        return;
    }
    $catalog = $menuCatalog();
    view('staff/table_detail', [
        'title' => 'Kasa · ' . $table['label'],
        'table' => $table,
        'orders' => OrderService::openOrdersForTable((int) $id),
        'items' => $catalog['items'],
        'categories' => $catalog['categories'],
        'user' => Auth::user(),
        'mode' => 'cashier',
        'canPay' => true,
        'canCancel' => true,
        'canClose' => true,
        'canAddToOrder' => static fn(array $order): bool => true,
        'canEditItemNote' => static fn(array $order): bool => true,
    ]);
});

$router->post('/api/orders/{id}/status', static function (string $id): void {
    Auth::requireRole('cashier', 'admin', 'waiter');
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    $status = (string) ($payload['status'] ?? '');
    if (Auth::role() === 'waiter' && in_array($status, ['paid', 'cancelled'], true)) {
        json_response(['ok' => false, 'error' => 'Garson sipariş iptal edemez veya tahsilat yapamaz.'], 403);
    }
    try {
        OrderService::updateStatus((int) $id, $status, Auth::id());
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

$router->post('/api/orders/{id}/pay', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    try {
        OrderService::payOrder((int) $id, (string) ($payload['payment_method'] ?? ''), Auth::id());
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

$router->post('/api/orders/{id}/cancel', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    try {
        OrderService::cancelOrder((int) $id, Auth::id());
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

$router->post('/api/order-items/{id}/cancel', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    try {
        OrderService::cancelItem((int) $id, Auth::id());
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

$router->post('/api/order-items/{id}/note', static function (string $id): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    $itemId = (int) $id;
    $pdo = Database::pdo();
    $stmt = $pdo->prepare(
        'SELECT oi.id, o.waiter_id, o.status
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE oi.id = ? LIMIT 1'
    );
    $stmt->execute([$itemId]);
    $row = $stmt->fetch();
    if (!$row) {
        json_response(['ok' => false, 'error' => 'Ürün bulunamadı'], 404);
    }
    if (Auth::role() === 'waiter' && (int) ($row['waiter_id'] ?? 0) !== (int) Auth::id()) {
        json_response(['ok' => false, 'error' => 'Sadece kendi siparişinize not yazabilirsiniz.'], 403);
    }
    try {
        OrderService::updateItemNote($itemId, (string) ($payload['note'] ?? ''), Auth::id());
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

$router->post('/api/tables/{id}/close', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    try {
        OrderService::closeTable((int) $id, (string) ($payload['payment_method'] ?? ''), Auth::id());
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

// Kasa / Yönetici — Fatura & Gün sonu (Türkiye satış belgesi + gün kapanışı)
$router->get('/kasa/gun-sonu', static function (): void {
    Auth::requireRole('cashier', 'admin');
    $role = Auth::role();
    $today = date('Y-m-d');
    $date = trim((string) ($_GET['date'] ?? $today));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = $today;
    }
    // Kasa yalnızca bugünün gün sonunu görür; yönetici tüm tarihler.
    if ($role === 'cashier') {
        $date = $today;
    }
    view('staff/day_close', [
        'title' => 'Gün sonu',
        'user' => Auth::user(),
        'date' => $date,
        'summary' => FiscalService::daySummary($date),
        'recent' => $role === 'admin' ? FiscalService::recentDayCloses(30) : [],
        'company' => FiscalService::companyProfile(),
        'canBrowseDates' => $role === 'admin',
        'canManageFiscalSettings' => $role === 'admin',
    ]);
});

$router->post('/kasa/gun-sonu', static function (): void {
    Auth::requireRole('cashier', 'admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/kasa/gun-sonu');
    }
    $role = Auth::role();
    $today = date('Y-m-d');
    $date = trim((string) input('date'));
    if ($role === 'cashier') {
        $date = $today;
    }
    try {
        FiscalService::closeDay($date, Auth::id(), (string) input('note'), false);
        flash('success', 'Gün sonu kapatıldı: ' . $date);
        redirect('/kasa/gun-sonu?date=' . urlencode($date));
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect('/kasa/gun-sonu?date=' . urlencode($role === 'cashier' ? $today : ($date !== '' ? $date : $today)));
    }
});

// Yönetici — Günü raporlar (otomatik + manuel gün sonu listesi)
$router->get('/yonetici/gun-raporlari', static function (): void {
    Auth::requireRole('admin');
    $date = trim((string) ($_GET['date'] ?? ''));
    $summary = null;
    if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $summary = FiscalService::daySummary($date);
    }
    view('staff/admin_day_reports', [
        'title' => 'Günü raporlar',
        'user' => Auth::user(),
        'reports' => FiscalService::recentDayCloses(90),
        'date' => $date,
        'summary' => $summary,
        'company' => FiscalService::companyProfile(),
    ]);
});

$router->get('/kasa/faturalar', static function (): void {
    Auth::requireRole('cashier', 'admin');
    $role = Auth::role();
    $today = date('Y-m-d');
    if ($role === 'cashier') {
        $stmt = Database::pdo()->prepare(
            'SELECT i.*, o.order_code
             FROM invoices i
             JOIN orders o ON o.id = i.order_id
             WHERE i.invoice_date = ?
             ORDER BY i.id DESC
             LIMIT 100'
        );
        $stmt->execute([$today]);
        $rows = $stmt->fetchAll();
    } else {
        $date = trim((string) ($_GET['date'] ?? ''));
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $stmt = Database::pdo()->prepare(
                'SELECT i.*, o.order_code
                 FROM invoices i
                 JOIN orders o ON o.id = i.order_id
                 WHERE i.invoice_date = ?
                 ORDER BY i.id DESC
                 LIMIT 200'
            );
            $stmt->execute([$date]);
            $rows = $stmt->fetchAll();
        } else {
            $rows = Database::pdo()->query(
                'SELECT i.*, o.order_code
                 FROM invoices i
                 JOIN orders o ON o.id = i.order_id
                 ORDER BY i.id DESC
                 LIMIT 200'
            )->fetchAll();
            $date = '';
        }
    }
    view('staff/invoices', [
        'title' => 'Satış faturaları',
        'user' => Auth::user(),
        'invoices' => $rows,
        'canBrowseDates' => $role === 'admin',
        'filterDate' => $role === 'admin' ? ($date ?? '') : $today,
    ]);
});

$router->get('/kasa/fatura-ayarlar', static function (): void {
    Auth::requireRole('admin');
    view('staff/fiscal_settings', [
        'title' => 'Firma / KDV ayarları',
        'user' => Auth::user(),
        'company' => FiscalService::companyProfile(),
    ]);
});

$router->post('/kasa/fatura-ayarlar', static function (): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/kasa/fatura-ayarlar');
    }
    try {
        FiscalService::saveCompanyProfile([
            'title' => (string) input('title'),
            'vkn' => (string) input('vkn'),
            'tax_office' => (string) input('tax_office'),
            'address' => (string) input('address'),
            'city' => (string) input('city'),
            'phone' => (string) input('phone'),
            'vat_rate' => (string) input('vat_rate'),
        ]);
        flash('success', 'Firma / KDV bilgileri kaydedildi.');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }
    redirect('/kasa/fatura-ayarlar');
});

$router->get('/kasa/fatura/siparis/{id}', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    $order = OrderService::findById((int) $id);
    if (!$order) {
        http_response_code(404);
        echo 'Sipariş bulunamadı';
        return;
    }
    if (($order['status'] ?? '') !== 'paid') {
        flash('error', 'Fatura için sipariş önce ödenmelidir.');
        redirect('/kasa');
    }
    $paidDay = substr((string) ($order['paid_at'] ?? date('Y-m-d')), 0, 10);
    if (Auth::role() === 'cashier' && $paidDay !== date('Y-m-d')) {
        flash('error', 'Kasa yalnızca bugün ödenen siparişler için fatura kesebilir.');
        redirect('/kasa/faturalar');
    }
    $invoice = FiscalService::findInvoiceByOrder((int) $id);
    if ($invoice) {
        redirect('/kasa/fatura/' . (int) $invoice['id']);
    }
    view('staff/invoice_issue', [
        'title' => 'Fatura kes',
        'user' => Auth::user(),
        'order' => $order,
        'invoice' => null,
    ]);
});

$router->post('/kasa/fatura/siparis/{id}', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/kasa/fatura/siparis/' . (int) $id);
    }
    $order = OrderService::findById((int) $id);
    if ($order && Auth::role() === 'cashier') {
        $paidDay = substr((string) ($order['paid_at'] ?? ''), 0, 10);
        if ($paidDay !== date('Y-m-d')) {
            flash('error', 'Kasa yalnızca bugün ödenen siparişler için fatura kesebilir.');
            redirect('/kasa/faturalar');
        }
    }
    try {
        $invoice = FiscalService::issueForOrder((int) $id, Auth::id(), [
            'name' => (string) input('buyer_name'),
            'tax_id' => (string) input('buyer_tax_id'),
            'tax_office' => (string) input('buyer_tax_office'),
            'address' => (string) input('buyer_address'),
        ]);
        flash('success', 'Fatura kesildi: ' . $invoice['invoice_no']);
        redirect('/kasa/fatura/' . (int) $invoice['id']);
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect('/kasa/fatura/siparis/' . (int) $id);
    }
});

$router->get('/kasa/fatura/{id}', static function (string $id): void {
    Auth::requireRole('cashier', 'admin');
    $invoice = FiscalService::findInvoice((int) $id);
    if (!$invoice) {
        http_response_code(404);
        echo 'Fatura bulunamadı';
        return;
    }
    // Kasa yalnızca bugünün faturalarını açabilir.
    if (Auth::role() === 'cashier' && (string) ($invoice['invoice_date'] ?? '') !== date('Y-m-d')) {
        flash('error', 'Kasa yalnızca bugünün faturalarını görüntüleyebilir.');
        redirect('/kasa/faturalar');
    }
    $lines = json_decode((string) ($invoice['lines_json'] ?? '[]'), true);
    view('staff/invoice', [
        'title' => 'Fatura ' . $invoice['invoice_no'],
        'user' => Auth::user(),
        'invoice' => $invoice,
        'lines' => is_array($lines) ? $lines : [],
    ]);
});

$router->post('/api/orders/{id}/note', static function (string $id): void {
    Auth::requireRole('cashier', 'admin', 'waiter');
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    $note = (string) ($payload['note'] ?? '');
    try {
        OrderService::updateNote((int) $id, $note, Auth::id());
        json_response(['ok' => true, 'note' => trim($note)]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
});

// Admin — Restoran kontrol
$adminSalesSummary = static function (string $from, string $to): array {
    $pdo = Database::pdo();
    $summary = $pdo->prepare(
        "SELECT
            COUNT(*) AS order_count,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END),0) AS paid_total,
            COALESCE(SUM(CASE WHEN source = 'online' AND status = 'paid' THEN total ELSE 0 END),0) AS online_total,
            COALESCE(SUM(CASE WHEN source = 'waiter' AND status = 'paid' THEN total ELSE 0 END),0) AS waiter_total,
            COALESCE(SUM(CASE WHEN source = 'cashier' AND status = 'paid' THEN total ELSE 0 END),0) AS cashier_total,
            COALESCE(SUM(CASE WHEN status = 'paid' AND payment_method = 'cash' THEN total ELSE 0 END),0) AS cash_total,
            COALESCE(SUM(CASE WHEN status = 'paid' AND payment_method = 'card' THEN total ELSE 0 END),0) AS card_total,
            COALESCE(SUM(CASE WHEN status NOT IN ('paid','cancelled') THEN total ELSE 0 END),0) AS open_total
         FROM orders
         WHERE created_at BETWEEN ? AND ?"
    );
    $summary->execute([$from, $to]);
    return $summary->fetch() ?: [];
};

$adminStaffStats = static function (string $role, string $from, string $to): array {
    $pdo = Database::pdo();
    $stmt = $pdo->prepare(
        "SELECT s.id, s.name,
            COUNT(o.id) AS order_count,
            COALESCE(SUM(CASE WHEN o.status = 'paid' THEN o.total ELSE 0 END),0) AS sales_total
         FROM staff s
         LEFT JOIN orders o ON o.waiter_id = s.id AND o.created_at BETWEEN ? AND ?
         WHERE s.role = ?
         GROUP BY s.id, s.name
         ORDER BY sales_total DESC, s.name ASC"
    );
    $stmt->execute([$from, $to, $role]);
    return $stmt->fetchAll();
};

$adminLiveStats = static function () use ($adminSalesSummary): array {
    $dayFrom = date('Y-m-d 00:00:00');
    $dayTo = date('Y-m-d 23:59:59');
    $today = $adminSalesSummary($dayFrom, $dayTo);
    $openTables = array_values(array_filter(
        OrderService::tablesOverview(),
        static fn(array $t): bool => !empty($t['is_open'])
    ));
    $pdo = Database::pdo();
    $pendingOnline = (int) $pdo->query(
        "SELECT COUNT(*) FROM orders WHERE source = 'online' AND status = 'pending'"
    )->fetchColumn();
    $kitchenQueued = (int) $pdo->query(
        "SELECT COUNT(*) FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE oi.station = 'kitchen'
           AND oi.status IN ('queued','preparing')
           AND o.status NOT IN ('cancelled','paid','pending')"
    )->fetchColumn();
    $barQueued = (int) $pdo->query(
        "SELECT COUNT(*) FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE oi.station = 'bar'
           AND oi.status IN ('queued','preparing')
           AND o.status NOT IN ('cancelled','paid','pending')"
    )->fetchColumn();
    $recent = OrderService::listRecent([], 8);
    return [
        'today' => [
            'order_count' => (int) ($today['order_count'] ?? 0),
            'paid_total' => (float) ($today['paid_total'] ?? 0),
            'open_total' => (float) ($today['open_total'] ?? 0),
            'online_total' => (float) ($today['online_total'] ?? 0),
            'waiter_total' => (float) ($today['waiter_total'] ?? 0),
            'cashier_total' => (float) ($today['cashier_total'] ?? 0),
        ],
        'open_tables' => array_map(static function (array $t): array {
            return [
                'id' => (int) $t['id'],
                'label' => (string) ($t['label'] ?? ''),
                'code' => (string) ($t['code'] ?? ''),
                'open_count' => (int) ($t['open_count'] ?? 0),
                'open_total' => (float) ($t['open_total'] ?? 0),
                'waiter_names' => $t['waiter_names'] ?? [],
            ];
        }, $openTables),
        'pending_online' => $pendingOnline,
        'kitchen_queued' => $kitchenQueued,
        'bar_queued' => $barQueued,
        'recent' => array_map(static function (array $o): array {
            return [
                'order_code' => (string) $o['order_code'],
                'source' => (string) $o['source'],
                'status' => (string) $o['status'],
                'total' => (float) $o['total'],
            ];
        }, $recent),
        'updated_at' => date('H:i:s'),
    ];
};

$router->get('/yonetici', static function () use ($adminLiveStats): void {
    Auth::requireRole('admin');
    view('staff/admin', [
        'title' => 'Yönetici · Restoran kontrol',
        'user' => Auth::user(),
        'live' => $adminLiveStats(),
    ]);
});

$router->get('/api/admin/live-stats', static function () use ($adminLiveStats): void {
    Auth::requireRole('admin');
    json_response(['ok' => true, 'live' => $adminLiveStats()]);
});

$router->get('/yonetici/masalar', static function (): void {
    Auth::requireRole('admin');
    $pdo = Database::pdo();
    $all = $pdo->query('SELECT * FROM dining_tables ORDER BY id')->fetchAll();
    $openMap = [];
    foreach (OrderService::tablesOverview() as $row) {
        $openMap[(int) $row['id']] = $row;
    }
    foreach ($all as &$table) {
        $id = (int) $table['id'];
        $open = $openMap[$id] ?? null;
        $table['is_open'] = $open['is_open'] ?? false;
        $table['open_count'] = $open['open_count'] ?? 0;
        $table['open_total'] = $open['open_total'] ?? 0;
        $table['waiter_names'] = $open['waiter_names'] ?? [];
    }
    unset($table);
    view('staff/admin_tables', [
        'title' => 'Yönetici · Masalar',
        'tables' => $all,
        'user' => Auth::user(),
    ]);
});

$router->get('/yonetici/masalar/ekle', static function (): void {
    Auth::requireRole('admin');
    view('staff/table_add', [
        'title' => 'Yönetici · Masa ekle',
        'user' => Auth::user(),
        'staffOptions' => TableService::staffOptions(),
        'canPickOpener' => true,
        'backUrl' => url('/yonetici/masalar'),
        'formAction' => url('/masa/ekle'),
        'roleLabel' => 'Yönetici',
    ]);
});

$router->post('/yonetici/masalar/ekle', static function (): void {
    // Eski form URL'si — ortak handler'a yönlendir
    Auth::requireRole('admin');
    $_POST['_csrf'] = $_POST['_csrf'] ?? '';
    // Reuse by internal redirect of POST body via shared endpoint logic
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/masa/ekle');
    }
    $masaNo = trim((string) (input('masa_no') ?: input('code')));
    $label = trim((string) input('label'));
    if ($masaNo === '' && $label !== '') {
        $masaNo = $label;
    }
    $seats = (int) (input('seats') ?: 4);
    $openedByStaffId = (int) input('opened_by_staff_id') ?: (int) Auth::id();
    $openedByName = trim((string) (input('opened_by_name') ?: (Auth::user()['name'] ?? '')));
    if ($openedByStaffId > 0) {
        $stmt = Database::pdo()->prepare('SELECT name FROM staff WHERE id = ? LIMIT 1');
        $stmt->execute([$openedByStaffId]);
        $name = $stmt->fetchColumn();
        if ($name) {
            $openedByName = (string) $name;
        }
    }
    try {
        $table = TableService::create($masaNo, $seats, $openedByStaffId ?: null, $openedByName);
        flash('success', $table['label'] . ' eklendi.');
        redirect('/yonetici/masalar');
    } catch (Throwable $e) {
        flash('error', $e->getMessage() !== '' ? $e->getMessage() : 'Masa eklenemedi.');
        redirect('/masa/ekle');
    }
});

$router->post('/yonetici/masalar/durum', static function (): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici/masalar');
    }
    $tableId = (int) input('table_id');
    $active = (string) input('is_active') === '1' ? 1 : 0;
    Database::pdo()->prepare('UPDATE dining_tables SET is_active = ? WHERE id = ?')->execute([$active, $tableId]);
    flash('success', $active ? 'Masa aktifleştirildi.' : 'Masa pasife alındı.');
    redirect('/yonetici/masalar');
});

$router->get('/yonetici/masalar/{id}', static function (string $id): void {
    Auth::requireRole('admin');
    $stmt = Database::pdo()->prepare('SELECT * FROM dining_tables WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $id]);
    $table = $stmt->fetch();
    if (!$table) {
        http_response_code(404);
        echo 'Masa bulunamadı';
        return;
    }
    view('staff/admin_table_edit', [
        'title' => 'Yönetici · Masa düzenle',
        'table' => $table,
        'user' => Auth::user(),
    ]);
});

$router->post('/yonetici/masalar/{id}', static function (string $id): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici/masalar/' . (int) $id);
    }
    $code = strtoupper(trim((string) input('code')));
    $label = trim((string) input('label'));
    $seats = max(1, min(50, (int) input('seats')));
    $active = input('is_active') ? 1 : 0;
    if ($code === '' || $label === '' || !preg_match('/^[A-Z0-9_-]+$/', $code)) {
        flash('error', 'Masa bilgileri geçersiz.');
        redirect('/yonetici/masalar/' . (int) $id);
    }
    try {
        Database::pdo()->prepare(
            'UPDATE dining_tables SET code = ?, label = ?, seats = ?, is_active = ? WHERE id = ?'
        )->execute([$code, $label, $seats, $active, (int) $id]);
        flash('success', 'Masa güncellendi.');
        redirect('/yonetici/masalar');
    } catch (Throwable) {
        flash('error', 'Güncellenemedi (kod benzersiz olmalı).');
        redirect('/yonetici/masalar/' . (int) $id);
    }
});

$router->get('/yonetici/urunler', static function (): void {
    Auth::requireRole('admin');
    $pdo = Database::pdo();
    $items = $pdo->query(
        'SELECT m.*, c.name AS category_name
         FROM menu_items m
         LEFT JOIN categories c ON c.id = m.category_id
         ORDER BY c.sort_order, m.sort_order, m.id'
    )->fetchAll();
    $categories = $pdo->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    view('staff/admin_products', [
        'title' => 'Yönetici · Ürünler',
        'items' => $items,
        'categories' => $categories,
        'user' => Auth::user(),
    ]);
});

$router->get('/yonetici/urunler/ekle', static function (): void {
    Auth::requireRole('admin');
    $categories = Database::pdo()
        ->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, id')
        ->fetchAll();
    view('staff/admin_product_form', [
        'title' => 'Yönetici · Ürün ekle',
        'item' => null,
        'categories' => $categories,
        'user' => Auth::user(),
    ]);
});

$router->post('/yonetici/urunler/ekle', static function (): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici/urunler/ekle');
    }
    $name = trim((string) input('name'));
    $description = trim((string) input('description'));
    $categoryId = (int) input('category_id');
    $price = (float) input('price');
    $vatRate = FiscalService::normalizeVatRate(input('vat_rate'));
    $station = (string) input('station');
    $sort = max(0, (int) input('sort_order'));
    $available = input('is_available') ? 1 : 0;
    $imageUrl = trim((string) input('image_url'));
    if ($name === '' || $categoryId <= 0 || $price < 0 || !in_array($station, ['kitchen', 'bar'], true)) {
        flash('error', 'Ürün bilgileri geçersiz.');
        redirect('/yonetici/urunler/ekle');
    }
    if ($imageUrl === '' && isset(MenuImageSync::catalog()[$name])) {
        $imageUrl = MenuImageSync::catalog()[$name];
    }
    Database::pdo()->prepare(
        'INSERT INTO menu_items (category_id, name, description, price, vat_rate, station, is_available, image_url, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $categoryId,
        $name,
        $description !== '' ? $description : null,
        $price,
        $vatRate,
        $station,
        $available,
        $imageUrl !== '' ? $imageUrl : null,
        $sort,
    ]);
    flash('success', 'Ürün eklendi.');
    redirect('/yonetici/urunler');
});

$router->post('/yonetici/urunler/durum', static function (): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici/urunler');
    }
    $itemId = (int) input('item_id');
    $available = (string) input('is_available') === '1' ? 1 : 0;
    Database::pdo()->prepare('UPDATE menu_items SET is_available = ? WHERE id = ?')->execute([$available, $itemId]);
    flash('success', $available ? 'Ürün satışa açıldı.' : 'Ürün satıştan kaldırıldı.');
    redirect('/yonetici/urunler');
});

$router->get('/yonetici/urunler/{id}', static function (string $id): void {
    Auth::requireRole('admin');
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $id]);
    $item = $stmt->fetch();
    if (!$item) {
        http_response_code(404);
        echo 'Ürün bulunamadı';
        return;
    }
    $categories = $pdo->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    view('staff/admin_product_form', [
        'title' => 'Yönetici · Ürün düzenle',
        'item' => $item,
        'categories' => $categories,
        'user' => Auth::user(),
    ]);
});

$router->post('/yonetici/urunler/{id}', static function (string $id): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici/urunler/' . (int) $id);
    }
    $name = trim((string) input('name'));
    $description = trim((string) input('description'));
    $categoryId = (int) input('category_id');
    $price = (float) input('price');
    $vatRate = FiscalService::normalizeVatRate(input('vat_rate'));
    $station = (string) input('station');
    $sort = max(0, (int) input('sort_order'));
    $available = input('is_available') ? 1 : 0;
    $imageUrl = trim((string) input('image_url'));
    if ($name === '' || $categoryId <= 0 || $price < 0 || !in_array($station, ['kitchen', 'bar'], true)) {
        flash('error', 'Ürün bilgileri geçersiz.');
        redirect('/yonetici/urunler/' . (int) $id);
    }
    if ($imageUrl === '' && isset(MenuImageSync::catalog()[$name])) {
        $imageUrl = MenuImageSync::catalog()[$name];
    }
    Database::pdo()->prepare(
        'UPDATE menu_items
         SET category_id = ?, name = ?, description = ?, price = ?, vat_rate = ?, station = ?, is_available = ?, image_url = ?, sort_order = ?
         WHERE id = ?'
    )->execute([
        $categoryId,
        $name,
        $description !== '' ? $description : null,
        $price,
        $vatRate,
        $station,
        $available,
        $imageUrl !== '' ? $imageUrl : null,
        $sort,
        (int) $id,
    ]);
    flash('success', 'Ürün güncellendi.');
    redirect('/yonetici/urunler');
});

$router->get('/yonetici/istatistikler', static function () use ($adminSalesSummary): void {
    Auth::requireRole('admin');
    $monthStart = date('Y-m-01 00:00:00');
    $monthEnd = date('Y-m-t 23:59:59');
    $dayStart = date('Y-m-d 00:00:00');
    $dayEnd = date('Y-m-d 23:59:59');
    view('staff/admin_sales', [
        'title' => 'Yönetici · Satış istatistikleri',
        'stats' => $adminSalesSummary($monthStart, $monthEnd),
        'dayStats' => $adminSalesSummary($dayStart, $dayEnd),
        'monthLabel' => date('F Y'),
        'user' => Auth::user(),
    ]);
});

$router->get('/yonetici/siparisler', static function (): void {
    Auth::requireRole('admin');
    view('staff/admin_orders', [
        'title' => 'Yönetici · Siparişler',
        'orders' => OrderService::listRecent([], 120),
        'user' => Auth::user(),
    ]);
});

$router->get('/yonetici/personel-istatistik', static function () use ($adminStaffStats): void {
    Auth::requireRole('admin');
    $monthStart = date('Y-m-01 00:00:00');
    $monthEnd = date('Y-m-t 23:59:59');
    view('staff/admin_staff_stats', [
        'title' => 'Yönetici · Personel istatistik',
        'waiterStats' => $adminStaffStats('waiter', $monthStart, $monthEnd),
        'cashierStats' => $adminStaffStats('cashier', $monthStart, $monthEnd),
        'monthLabel' => date('F Y'),
        'user' => Auth::user(),
    ]);
});

$router->get('/yonetici/personel', static function (): void {
    Auth::requireRole('admin');
    $staff = Database::pdo()
        ->query('SELECT id, name, username, role, is_active, created_at FROM staff ORDER BY is_active DESC, role, name')
        ->fetchAll();
    view('staff/admin_staff', [
        'title' => 'Yönetici · Personel takip',
        'staff' => $staff,
        'user' => Auth::user(),
    ]);
});

$router->get('/yonetici/franchise', static function (): void {
    Auth::requireRole('admin');
    $filter = trim((string) input('durum'));
    if ($filter !== '' && !in_array($filter, FranchiseService::STATUSES, true)) {
        $filter = '';
    }
    view('staff/admin_franchise', [
        'title' => 'Yönetici · Franchise başvuruları',
        'applications' => FranchiseService::list($filter !== '' ? $filter : null),
        'counts' => FranchiseService::countsByStatus(),
        'filter' => $filter,
        'user' => Auth::user(),
    ]);
});

$router->post('/yonetici/franchise/{id}/durum', static function (string $id): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici/franchise');
    }
    try {
        FranchiseService::updateStatus(
            (int) $id,
            (string) input('status'),
            (string) input('admin_note')
        );
        flash('success', 'Başvuru durumu güncellendi.');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }
    $back = trim((string) input('redirect'));
    redirect($back !== '' ? $back : '/yonetici/franchise');
});

$router->get('/yonetici/personel/ekle', static function (): void {
    Auth::requireRole('admin');
    view('staff/admin_staff_add', [
        'title' => 'Yönetici · Personel ekle',
        'user' => Auth::user(),
    ]);
});

$router->get('/yonetici/personel/cikar', static function (): void {
    Auth::requireRole('admin');
    $staff = Database::pdo()
        ->query('SELECT id, name, username, role, is_active, created_at FROM staff ORDER BY is_active DESC, role, name')
        ->fetchAll();
    view('staff/admin_staff_remove', [
        'title' => 'Yönetici · Garson sil',
        'staff' => $staff,
        'user' => Auth::user(),
    ]);
});

$router->post('/yonetici/personel', static function (): void {
    Auth::requireRole('admin');
    $redirect = (string) input('redirect');
    if ($redirect === '' || !str_starts_with($redirect, '/yonetici')) {
        $redirect = '/yonetici/personel/ekle';
    }
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect($redirect);
    }
    $name = trim((string) input('name'));
    $username = trim((string) input('username'));
    $password = (string) input('password');
    $role = (string) input('role');
    if ($name === '' || $username === '' || strlen($password) < 6 || !in_array($role, ['admin', 'cashier', 'waiter'], true)) {
        flash('error', 'Personel bilgileri geçersiz.');
        redirect($redirect);
    }
    try {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO staff (name, username, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role]);
        flash('success', 'Personel eklendi (' . role_label($role) . ').');
        redirect('/yonetici/personel');
    } catch (Throwable $e) {
        flash('error', 'Personel eklenemedi: kullanıcı adı kullanılıyor olabilir.');
        redirect($redirect);
    }
});

$router->post('/yonetici/personel/cikar', static function (): void {
    Auth::requireRole('admin');
    $redirect = (string) input('redirect');
    if ($redirect === '' || !str_starts_with($redirect, '/yonetici')) {
        $redirect = '/yonetici/personel/cikar';
    }
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect($redirect);
    }
    $staffId = (int) input('staff_id');
    $roleGuard = trim((string) input('role_guard'));
    $hardDelete = (string) input('hard_delete') === '1';
    if ($staffId <= 0 || $staffId === (int) Auth::id()) {
        flash('error', 'Bu personel silinemez.');
        redirect($redirect);
    }

    $pdo = Database::pdo();
    SchemaSync::ensure();
    $stmt = $pdo->prepare('SELECT id, role, name FROM staff WHERE id = ? LIMIT 1');
    $stmt->execute([$staffId]);
    $member = $stmt->fetch();
    if (!$member) {
        flash('error', 'Personel bulunamadı.');
        redirect($redirect);
    }
    if ($roleGuard !== '' && (string) $member['role'] !== $roleGuard) {
        flash('error', 'Bu işlem yalnızca ' . role_label($roleGuard) . ' için geçerlidir.');
        redirect($redirect);
    }

    $label = role_label((string) $member['role']);
    $name = (string) $member['name'];

    if ($hardDelete && (string) $member['role'] === 'waiter') {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE orders SET waiter_id = NULL WHERE waiter_id = ?')->execute([$staffId]);
            $pdo->prepare('UPDATE order_events SET staff_id = NULL WHERE staff_id = ?')->execute([$staffId]);
            $del = $pdo->prepare('DELETE FROM staff WHERE id = ? AND role = ?');
            $del->execute([$staffId, 'waiter']);
            if ($del->rowCount() < 1) {
                throw new RuntimeException('Waiter row not deleted');
            }
            $pdo->commit();
            flash('success', 'Garson hesabı kalıcı olarak silindi: ' . $name);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('error', 'Garson silinemedi. Sipariş bağlantısını kontrol edin.');
        }
        redirect($redirect);
    }

    $pdo->prepare('UPDATE staff SET is_active = 0, updated_at = NOW() WHERE id = ?')->execute([$staffId]);
    flash('success', $label . ' pasife alındı: ' . $name);
    redirect($redirect);
});

$router->post('/yonetici/personel/aktif', static function (): void {
    Auth::requireRole('admin');
    $redirect = (string) input('redirect');
    if ($redirect === '' || !str_starts_with($redirect, '/yonetici')) {
        $redirect = '/yonetici/personel/cikar';
    }
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect($redirect);
    }
    $staffId = (int) input('staff_id');
    $stmt = Database::pdo()->prepare('UPDATE staff SET is_active = 1, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$staffId]);
    flash('success', 'Personel yeniden aktifleştirildi.');
    redirect($redirect);
});

$router->dispatch($method, $path);
