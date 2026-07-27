<?php

declare(strict_types=1);

require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/Database.php';
require __DIR__ . '/app/Auth.php';
require __DIR__ . '/app/OrderService.php';
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
}

$router = new Router();

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
    $catalog = $menuCatalog();
    $table = null;
    $token = trim((string) input('t', ''));
    if ($token !== '') {
        $stmt = Database::pdo()->prepare('SELECT * FROM dining_tables WHERE qr_token = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$token]);
        $table = $stmt->fetch() ?: null;
    }
    view('public/menu', [
        'title' => 'Menü',
        'categories' => $catalog['categories'],
        'items' => $catalog['items'],
        'table' => $table,
    ]);
});

$router->get('/siparis', static function () use ($menuCatalog): void {
    $catalog = $menuCatalog();
    view('public/order', [
        'title' => 'Online Sipariş',
        'categories' => $catalog['categories'],
        'items' => $catalog['items'],
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
        $order = OrderService::create([
            'source' => 'online',
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_note' => trim((string) ($payload['customer_note'] ?? '')),
            'items' => $payload['items'] ?? [],
        ]);
        json_response(['ok' => true, 'order' => [
            'id' => (int) $order['id'],
            'order_code' => $order['order_code'],
            'status' => $order['status'],
            'total' => (float) $order['total'],
        ]]);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    }
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

$router->get('/qr', static function (): void {
    Auth::requireRole('waiter', 'cashier', 'admin');
    $pdo = Database::pdo();
    $tables = $pdo->query('SELECT * FROM dining_tables WHERE is_active = 1 ORDER BY id')->fetchAll();
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    $base = $host !== ''
        ? ($scheme . '://' . $host . base_path())
        : rtrim((string) config('app_url'), '/');
    view('staff/qr', [
        'title' => 'QR Menü Kodları',
        'tables' => $tables,
        'menuUrl' => $base . '/menu',
        'base' => $base,
        'user' => Auth::user(),
    ]);
});

// Staff auth
$router->get('/personel/giris', static function (): void {
    if (Auth::check()) {
        $role = Auth::role();
        redirect(match ($role) {
            'admin' => '/yonetici',
            'cashier' => '/kasa',
            default => '/garson',
        });
    }
    view('staff/login', ['title' => 'Personel Girişi']);
});

$router->post('/personel/giris', static function (): void {
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'Oturum doğrulaması başarısız.');
        redirect('/personel/giris');
    }
    $ok = Auth::attempt((string) input('username'), (string) input('password'));
    if (!$ok) {
        flash('error', 'Kullanıcı adı veya parola hatalı.');
        redirect('/personel/giris');
    }
    $role = Auth::role();
    redirect(match ($role) {
        'admin' => '/yonetici',
        'cashier' => '/kasa',
        default => '/garson',
    });
});

$router->post('/personel/cikis', static function (): void {
    Auth::logout();
    redirect('/personel/giris');
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
        'canPay' => false,
        'canCancel' => $role === 'admin',
        'canClose' => false,
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
           AND o.status NOT IN ('cancelled','paid')
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
           AND o.status NOT IN ('cancelled','paid')
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
    view('staff/cashier', [
        'title' => 'Kasa',
        'tables' => $tables,
        'orders' => $orders,
        'onlineOpen' => $onlineOpen,
        'user' => Auth::user(),
    ]);
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

$router->get('/yonetici', static function (): void {
    Auth::requireRole('admin');
    view('staff/admin', [
        'title' => 'Yönetici · Restoran kontrol',
        'user' => Auth::user(),
    ]);
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
    view('staff/admin_table_add', [
        'title' => 'Yönetici · Masa ekle',
        'user' => Auth::user(),
    ]);
});

$router->post('/yonetici/masalar/ekle', static function (): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici/masalar/ekle');
    }
    $code = strtoupper(trim((string) input('code')));
    $label = trim((string) input('label'));
    $seats = max(1, min(50, (int) input('seats')));
    if ($code === '' || $label === '' || !preg_match('/^[A-Z0-9_-]+$/', $code)) {
        flash('error', 'Masa kodu veya adı geçersiz.');
        redirect('/yonetici/masalar/ekle');
    }
    try {
        $token = bin2hex(random_bytes(16));
        $stmt = Database::pdo()->prepare(
            'INSERT INTO dining_tables (code, label, seats, is_active, qr_token) VALUES (?, ?, ?, 1, ?)'
        );
        $stmt->execute([$code, $label, $seats, $token]);
        flash('success', 'Masa eklendi: ' . $label);
        redirect('/yonetici/masalar');
    } catch (Throwable $e) {
        flash('error', 'Masa eklenemedi (kod benzersiz olmalı).');
        redirect('/yonetici/masalar/ekle');
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
        'INSERT INTO menu_items (category_id, name, description, price, station, is_available, image_url, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $categoryId,
        $name,
        $description !== '' ? $description : null,
        $price,
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
         SET category_id = ?, name = ?, description = ?, price = ?, station = ?, is_available = ?, image_url = ?, sort_order = ?
         WHERE id = ?'
    )->execute([
        $categoryId,
        $name,
        $description !== '' ? $description : null,
        $price,
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
    $hardDelete = (string) input('hard_delete') === '1' || $roleGuard === 'waiter';
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
