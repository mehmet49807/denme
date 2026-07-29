<?php

declare(strict_types=1);

function config(?string $key = null, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require dirname(__DIR__) . '/config/config.php';
    }
    if ($key === null) {
        return $cfg;
    }
    $parts = explode('.', $key);
    $value = $cfg;
    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    $base = ($dir === '' || $dir === '/' || $dir === '.') ? '' : $dir;
    return $base;
}

function url(string $path = '/'): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    if ($path === '' || $path === '/') {
        return base_path() !== '' ? base_path() . '/' : '/';
    }
    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }
    return base_path() . $path;
}

/** Local asset URL with filemtime cache-buster. */
function asset_url(string $path): string
{
    if (!str_starts_with($path, '/') && !str_starts_with($path, 'http')) {
        $path = '/' . $path;
    }
    $url = url($path);
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $url;
    }
    $file = dirname(__DIR__) . $path;
    if (is_file($file)) {
        $url .= (str_contains($url, '?') ? '&' : '?') . 'v=' . filemtime($file);
    }
    return $url;
}

/** Brand mark URL (PNG — tarayıcı önbelleğini kırar, her yerde görünür). */
function logo_url(): string
{
    $candidates = [
        '/assets/img/brand-crisp-co-v6.png',
        '/assets/img/brand-crisp-co-v5.png',
        '/assets/img/brand-crisp-co-v4.png',
        '/assets/img/brand-crisp-co-v3.png',
        '/assets/img/logo-crisp-co.png',
        '/assets/img/logo-crisp.png',
        '/assets/img/logo-mark.png',
        '/assets/img/logo.png',
    ];
    foreach ($candidates as $rel) {
        $png = dirname(__DIR__) . $rel;
        if (is_file($png)) {
            return asset_url($rel);
        }
    }
    return asset_url('/assets/img/logo.svg');
}

function redirect(string $path): never
{
    if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
        $path = url($path);
    }
    header('Location: ' . $path);
    exit;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function money(float|string $amount): string
{
    return number_format((float) $amount, 2, ',', '.') . ' ₺';
}

/** Türkiye KDV oranı gösterimi: %10 */
function format_vat_rate(float|string|null $rate): string
{
    $value = (float) str_replace(',', '.', (string) ($rate ?? 10));
    return '%' . rtrim(rtrim(number_format($value, 2, ',', ''), '0'), ',');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

/** JSON API CSRF: body `_csrf` or `X-CSRF-Token` header. */
function request_csrf_token(): ?string
{
    $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (is_string($header) && $header !== '') {
        return $header;
    }
    return null;
}

function require_csrf(?string $token = null): void
{
    $token = $token ?? (string) (input('_csrf') ?: request_csrf_token() ?: '');
    if (!verify_csrf($token)) {
        if (str_starts_with(current_path(), '/api/')) {
            json_response(['ok' => false, 'error' => 'CSRF doğrulaması başarısız'], 419);
        }
        flash('error', 'Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.');
        redirect('/');
    }
}

function require_json_csrf(?array $payload = null): void
{
    $token = null;
    if (is_array($payload) && isset($payload['_csrf'])) {
        $token = (string) $payload['_csrf'];
    }
    if ($token === null || $token === '') {
        $token = request_csrf_token();
    }
    if (!verify_csrf($token)) {
        json_response(['ok' => false, 'error' => 'CSRF doğrulaması başarısız'], 419);
    }
}

function ops_secret(): string
{
    $secret = trim((string) config('ops_secret', ''));
    if ($secret === '') {
        $secret = trim((string) (getenv('CHICKEN_OPS_SECRET') ?: ''));
    }
    return $secret;
}

function require_ops_secret(?string $provided = null): void
{
    $secret = ops_secret();
    $provided = $provided ?? (string) ($_GET['key'] ?? $_SERVER['HTTP_X_OPS_KEY'] ?? '');
    if ($secret === '' || !hash_equals($secret, $provided)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/** Only allow same-app relative redirects (anti open-redirect). */
function safe_internal_path(?string $path, string $fallback = '/'): string
{
    $path = trim((string) $path);
    if ($path === '' || str_starts_with($path, '//') || str_contains($path, '://')) {
        return $fallback;
    }
    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }
    return $path;
}

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    session_name((string) config('session_name', 'chicken_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => base_path() !== '' ? base_path() . '/' : '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function request_is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function view(string $name, array $data = [], ?string $layout = null): void
{
    $viewFile = dirname(__DIR__) . '/views/' . $name . '.php';
    if (!is_file($viewFile)) {
        http_response_code(500);
        echo 'View not found: ' . e($name);
        exit;
    }

    extract($data, EXTR_SKIP);
    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    $layoutFile = null;
    if (!empty($data['layout'])) {
        $layoutFile = dirname(__DIR__) . '/views/layouts/' . $data['layout'] . '.php';
    } elseif (str_starts_with($name, 'public/') || str_starts_with($name, 'errors/')) {
        $layoutFile = dirname(__DIR__) . '/views/layouts/public.php';
    } elseif (str_starts_with($name, 'staff/') && $name !== 'staff/login') {
        $layoutFile = dirname(__DIR__) . '/views/layouts/staff.php';
    }

    if ($layoutFile && is_file($layoutFile)) {
        require $layoutFile;
        return;
    }

    echo $content;
}

function partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require dirname(__DIR__) . '/views/' . $name . '.php';
}

function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = is_string($uri) ? $uri : '/';
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base)) ?: '/';
    }
    return rtrim($path, '/') ?: '/';
}

function is_active_path(string $path): bool
{
    return current_path() === rtrim($path, '/') || current_path() === $path;
}

function station_label(string $station): string
{
    return $station === 'bar' ? 'Bar' : 'Mutfak';
}

function status_label(string $status): string
{
    return match ($status) {
        'pending' => 'Bekliyor',
        'accepted' => 'Alındı',
        'preparing' => 'Hazırlanıyor',
        'ready' => 'Hazır',
        'served' => 'Servis edildi',
        'paid' => 'Ödendi',
        'cancelled' => 'İptal',
        'queued' => 'Sırada',
        default => $status,
    };
}

function source_label(string $source): string
{
    return match ($source) {
        'online' => 'Online',
        'cashier' => 'Kasa',
        default => 'Garson',
    };
}

/**
 * Mutfak/bar XPrinter fiş URL’si.
 *
 * @param array{autoprint?:bool,station?:string,items?:list<int>|string,back?:string} $opts
 */
function station_slip_url(int $orderId, array $opts = []): string
{
    $q = [];
    if (!empty($opts['autoprint'])) {
        $q['autoprint'] = '1';
    }
    $station = (string) ($opts['station'] ?? '');
    if (in_array($station, ['kitchen', 'bar', 'all'], true) && $station !== 'all') {
        $q['station'] = $station;
    }
    $items = $opts['items'] ?? null;
    if (is_array($items) && $items !== []) {
        $q['items'] = implode(',', array_map('intval', $items));
    } elseif (is_string($items) && trim($items) !== '') {
        $q['items'] = trim($items);
    }
    if (!empty($opts['back'])) {
        $q['back'] = (string) $opts['back'];
    }
    $path = '/garson/fis/' . $orderId;
    return $q === [] ? url($path) : url($path . '?' . http_build_query($q));
}

function slip_autoprint_enabled(): bool
{
    return BrochureService::getSetting('slip_autoprint', '1') !== '0';
}

/**
 * Yazdırılacak mutfak/bar kalemlerini sayar. Boş istasyon fişi üretilmez.
 *
 * @param list<int>|null $onlyItemIds null = tüm aktif kalemler
 * @return array{kitchen:int,bar:int,has_any:bool,station:string}
 */
function order_slip_station_counts(array $order, ?array $onlyItemIds = null): array
{
    $onlyMap = [];
    if (is_array($onlyItemIds) && $onlyItemIds !== []) {
        foreach ($onlyItemIds as $oid) {
            $id = (int) $oid;
            if ($id > 0) {
                $onlyMap[$id] = true;
            }
        }
    }

    $countStation = static function (array $rows) use ($onlyMap): int {
        $n = 0;
        foreach ($rows as $r) {
            if ((int) ($r['quantity'] ?? 0) <= 0) {
                continue;
            }
            if (($r['status'] ?? '') === 'cancelled') {
                continue;
            }
            if ($onlyMap !== [] && empty($onlyMap[(int) ($r['id'] ?? 0)])) {
                continue;
            }
            $n++;
        }
        return $n;
    };

    $kitchen = $countStation($order['kitchen_items'] ?? []);
    $bar = $countStation($order['bar_items'] ?? []);
    $station = 'all';
    if ($kitchen > 0 && $bar === 0) {
        $station = 'kitchen';
    } elseif ($bar > 0 && $kitchen === 0) {
        $station = 'bar';
    }

    return [
        'kitchen' => $kitchen,
        'bar' => $bar,
        'has_any' => ($kitchen + $bar) > 0,
        'station' => $station,
    ];
}

/**
 * Ürünü olan istasyonlar için fiş URL’si; hiç ürün yoksa null.
 *
 * @param list<int>|null $onlyItemIds
 * @param array{autoprint?:bool,back?:string} $opts
 */
function station_slip_url_for_order(array $order, ?array $onlyItemIds = null, array $opts = []): ?string
{
    $counts = order_slip_station_counts($order, $onlyItemIds);
    if (!$counts['has_any']) {
        return null;
    }

    return station_slip_url((int) $order['id'], [
        'autoprint' => !empty($opts['autoprint']),
        'station' => $counts['station'],
        'items' => $onlyItemIds ?? [],
        'back' => (string) ($opts['back'] ?? ''),
    ]);
}

function payment_method_label(?string $method): string
{
    return match ($method) {
        'cash', 'nakit' => 'Nakit',
        'card', 'kart' => 'Kart',
        default => '—',
    };
}

function payment_preference_label(?string $method): string
{
    return match ($method) {
        'cash', 'nakit' => 'Kapıda nakit',
        'card', 'kart' => 'Kapıda kart',
        default => '—',
    };
}

function role_label(string $role): string
{
    return match ($role) {
        'admin' => 'Yönetici',
        'cashier' => 'Kasa',
        'waiter' => 'Garson',
        'kitchen' => 'Mutfak',
        'bar' => 'Bar',
        default => $role,
    };
}

function admin_nav_active(string $prefix): bool
{
    $path = current_path();
    $prefix = rtrim($prefix, '/') ?: '/';
    if ($path === $prefix) {
        return true;
    }
    // Highlight parent section for nested edit pages like /yonetici/urunler/12
    if (preg_match('#^' . preg_quote($prefix, '#') . '/\d+#', $path)) {
        return true;
    }
    return false;
}

function generate_order_code(PDO $pdo): string
{
    $prefix = 'CHK';
    $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->execute(['order_prefix']);
    $row = $stmt->fetch();
    if ($row) {
        $prefix = (string) $row['setting_value'];
    }
    do {
        $code = sprintf('%s-%s-%s', $prefix, date('ymd'), strtoupper(bin2hex(random_bytes(2))));
        $check = $pdo->prepare('SELECT id FROM orders WHERE order_code = ? LIMIT 1');
        $check->execute([$code]);
    } while ($check->fetch());
    return $code;
}
