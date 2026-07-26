<?php

declare(strict_types=1);

require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/Database.php';
require __DIR__ . '/app/Auth.php';
require __DIR__ . '/app/OrderService.php';
require __DIR__ . '/app/CategorySync.php';
require __DIR__ . '/app/SchemaSync.php';
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

// Admin
$router->get('/yonetici', static function (): void {
    Auth::requireRole('admin');
    $pdo = Database::pdo();
    $monthStart = date('Y-m-01 00:00:00');
    $monthEnd = date('Y-m-t 23:59:59');

    $summary = $pdo->prepare(
        "SELECT
            COUNT(*) AS order_count,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END),0) AS paid_total,
            COALESCE(SUM(CASE WHEN source = 'online' AND status = 'paid' THEN total ELSE 0 END),0) AS online_total,
            COALESCE(SUM(CASE WHEN source = 'waiter' AND status = 'paid' THEN total ELSE 0 END),0) AS waiter_total
         FROM orders
         WHERE created_at BETWEEN ? AND ?"
    );
    $summary->execute([$monthStart, $monthEnd]);
    $stats = $summary->fetch() ?: [];

    $waiterStats = $pdo->prepare(
        "SELECT s.id, s.name,
            COUNT(o.id) AS order_count,
            COALESCE(SUM(CASE WHEN o.status = 'paid' THEN o.total ELSE 0 END),0) AS sales_total
         FROM staff s
         LEFT JOIN orders o ON o.waiter_id = s.id AND o.created_at BETWEEN ? AND ?
         WHERE s.role = 'waiter'
         GROUP BY s.id, s.name
         ORDER BY sales_total DESC"
    );
    $waiterStats->execute([$monthStart, $monthEnd]);

    $staff = $pdo->query('SELECT id, name, username, role, is_active, created_at FROM staff ORDER BY role, name')->fetchAll();
    $recent = OrderService::listRecent([], 50);

    view('staff/admin', [
        'title' => 'Yönetici Paneli',
        'stats' => $stats,
        'waiterStats' => $waiterStats->fetchAll(),
        'staff' => $staff,
        'orders' => $recent,
        'monthLabel' => date('F Y'),
        'user' => Auth::user(),
    ]);
});

$router->post('/yonetici/personel', static function (): void {
    Auth::requireRole('admin');
    if (!verify_csrf((string) input('_csrf'))) {
        flash('error', 'CSRF hatası');
        redirect('/yonetici');
    }
    $name = trim((string) input('name'));
    $username = trim((string) input('username'));
    $password = (string) input('password');
    $role = (string) input('role');
    if ($name === '' || $username === '' || strlen($password) < 6 || !in_array($role, ['admin', 'cashier', 'waiter'], true)) {
        flash('error', 'Personel bilgileri geçersiz.');
        redirect('/yonetici');
    }
    try {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO staff (name, username, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role]);
        flash('success', 'Personel eklendi.');
    } catch (Throwable $e) {
        flash('error', 'Personel eklenemedi: ' . $e->getMessage());
    }
    redirect('/yonetici');
});

$router->dispatch($method, $path);
