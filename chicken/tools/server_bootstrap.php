<?php

declare(strict_types=1);

/**
 * Temporary server-side bootstrap: copy chicken app into subdomain docroot if visible.
 * Delete after use.
 */
header('Content-Type: text/plain; charset=utf-8');

$key = $_GET['key'] ?? '';
$expected = $_GET['expect'] ?? '';
if ($expected === '' || !hash_equals($expected, $key)) {
    http_response_code(403);
    echo "forbidden\n";
    exit;
}

$source = realpath(__DIR__ . '/..');
if ($source === false) {
    echo "source missing\n";
    exit;
}

echo "source={$source}\n";
echo "php_uname=" . php_uname() . "\n";
echo "cwd=" . getcwd() . "\n";
echo "document_root=" . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";

$home = dirname($source, 3); // likely /home/USER or similar depending on nesting
$candidates = [
    $source, // already in a chicken folder
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../chicken.gonulkoprusu.com',
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../../chicken.gonulkoprusu.com',
    '/home/gonulkop/chicken.gonulkoprusu.com',
    '/home/gonulkoprusu/chicken.gonulkoprusu.com',
    '/home/gonulkop/public_html/../chicken.gonulkoprusu.com',
];

// Discover nearby directories
$probeRoots = array_unique(array_filter([
    dirname($source),
    dirname($source, 2),
    dirname($source, 3),
    dirname($source, 4),
    $_SERVER['DOCUMENT_ROOT'] ?? null,
    dirname((string) ($_SERVER['DOCUMENT_ROOT'] ?? '')),
    dirname((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), 2),
]));

foreach ($probeRoots as $root) {
    if (!$root || !is_dir($root)) {
        continue;
    }
    echo "listing {$root}\n";
    foreach (scandir($root) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $root . DIRECTORY_SEPARATOR . $entry;
        if (is_dir($full) && stripos($entry, 'chicken') !== false) {
            echo "found_dir {$full}\n";
            $candidates[] = $full;
            $pub = $full . '/public_html';
            if (is_dir($pub)) {
                echo "found_dir {$pub}\n";
                $candidates[] = $pub;
            }
        }
    }
}

$candidates = array_values(array_unique(array_filter($candidates)));
$target = null;
foreach ($candidates as $candidate) {
    $real = realpath($candidate) ?: $candidate;
    if (!is_dir($real)) {
        continue;
    }
    // Prefer dedicated domain folder, not the source itself
    if (realpath($real) === $source) {
        continue;
    }
    if (stripos($real, 'chicken.gonulkoprusu.com') !== false) {
        $target = $real;
        break;
    }
}

if ($target === null) {
    echo "NO_TARGET\n";
    echo "open_basedir=" . (ini_get('open_basedir') ?: '(none)') . "\n";
    exit;
}

echo "target={$target}\n";

function copy_tree(string $src, string $dst): int
{
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $rel = substr($item->getPathname(), strlen($src) + 1);
        if ($rel === false) {
            continue;
        }
        if (str_contains($rel, 'tools/server_bootstrap.php')) {
            continue;
        }
        $to = $dst . DIRECTORY_SEPARATOR . $rel;
        if ($item->isDir()) {
            if (!is_dir($to) && !mkdir($to, 0755, true) && !is_dir($to)) {
                throw new RuntimeException('mkdir failed: ' . $to);
            }
            continue;
        }
        $dir = dirname($to);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('mkdir failed: ' . $dir);
        }
        if (!copy($item->getPathname(), $to)) {
            throw new RuntimeException('copy failed: ' . $to);
        }
        $count++;
    }
    return $count;
}

function fetch_github(string $rel, string $branch = 'cursor/chicken-restaurant-site-7a42'): string
{
    $url = 'https://raw.githubusercontent.com/mehmet49807/denme/'
        . rawurlencode($branch)
        . '/chicken/'
        . implode('/', array_map('rawurlencode', explode('/', $rel)));
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'ChickenBootstrapRepair/1.0',
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code >= 400 || strlen((string) $body) < 20) {
            throw new RuntimeException("fetch failed {$rel} http={$code}");
        }
        return (string) $body;
    }
    $body = @file_get_contents($url);
    if ($body === false || strlen($body) < 20) {
        throw new RuntimeException("fetch failed {$rel}");
    }
    return $body;
}

// If a previous FTP timeout wiped index.php, restore critical files from GitHub first.
$repair = [
    'index.php',
    'app/helpers.php',
    'app/MenuImageSync.php',
    'app/MenuItemSync.php',
    'views/partials/menu_item_card.php',
    'assets/css/app.css',
    'assets/css/public-site.css',
];
foreach ($repair as $rel) {
    $path = $source . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $size = is_file($path) ? filesize($path) : 0;
    if ($size !== false && $size > 20) {
        continue;
    }
    try {
        $body = fetch_github($rel);
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('mkdir failed: ' . $dir);
        }
        if (file_put_contents($path, $body) === false) {
            throw new RuntimeException('write failed: ' . $path);
        }
        echo "REPAIRED_SOURCE {$rel} bytes=" . strlen($body) . "\n";
    } catch (Throwable $e) {
        echo "REPAIR_FAIL {$rel} " . $e->getMessage() . "\n";
    }
}

try {
    $copied = copy_tree($source, $target);
    echo "COPIED={$copied}\n";
    echo "OK\n";
} catch (Throwable $e) {
    echo "ERROR=" . $e->getMessage() . "\n";
}
