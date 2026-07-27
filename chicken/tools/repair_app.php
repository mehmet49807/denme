<?php

declare(strict_types=1);

/**
 * Emergency repair: pull critical app files from GitHub when FTP left empty files.
 * Delete after use if desired.
 */
header('Content-Type: text/plain; charset=utf-8');

$key = (string) ($_GET['key'] ?? '');
$expected = (string) ($_GET['expect'] ?? '');
if ($expected === '' || !hash_equals($expected, $key)) {
    http_response_code(403);
    echo "forbidden\n";
    exit;
}

$root = dirname(__DIR__);
$branch = (string) ($_GET['branch'] ?? 'cursor/chicken-restaurant-site-7a42');
$base = 'https://raw.githubusercontent.com/mehmet49807/denme/' . rawurlencode($branch) . '/chicken/';

$files = [
    'index.php',
    'app/helpers.php',
    'app/MenuImageSync.php',
    'app/MenuItemSync.php',
    'app/SchemaSync.php',
    'views/partials/menu_item_card.php',
    'views/public/menu.php',
    'views/public/home.php',
    'views/public/order.php',
    'views/public/menu_brochure.php',
    'views/layouts/public.php',
    'views/layouts/brochure.php',
    'views/staff/admin.php',
    'views/staff/qr.php',
    'app/CustomerAuth.php',
    'app/DiscountService.php',
    'assets/css/app.css',
    'assets/js/app.js',
    'assets/img/logo.svg',
    'assets/img/logo.png',
    'assets/img/logo-mark.png',
    'assets/img/logo-96.png',
    'assets/img/logo-192.png',
    'assets/img/logo-lezzet.png',
    'assets/img/logo-crisp.png',
    'assets/img/hero.svg',
];

function fetch_url(string $url): string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'ChickenRepair/1.0',
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($body === false || $code >= 400) {
            throw new RuntimeException("curl {$code} {$err}");
        }
        return (string) $body;
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 60,
            'header' => "User-Agent: ChickenRepair/1.0\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        throw new RuntimeException('file_get_contents failed');
    }
    return $body;
}

echo "root={$root}\n";
echo "branch={$branch}\n";
$ok = 0;
foreach ($files as $rel) {
    $url = $base . str_replace('%2F', '/', implode('/', array_map('rawurlencode', explode('/', $rel))));
    $dest = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    try {
        $body = fetch_url($url);
        if (strlen($body) < 20) {
            throw new RuntimeException('too small: ' . strlen($body));
        }
        $dir = dirname($dest);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('mkdir failed');
        }
        if (file_put_contents($dest, $body) === false) {
            throw new RuntimeException('write failed');
        }
        echo "OK {$rel} bytes=" . strlen($body) . "\n";
        $ok++;
    } catch (Throwable $e) {
        echo "FAIL {$rel} " . $e->getMessage() . "\n";
    }
}
echo "REPAIRED={$ok}\n";
echo $ok > 0 ? "OK\n" : "ERROR\n";
