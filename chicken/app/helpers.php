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
