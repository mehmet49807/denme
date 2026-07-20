<?php
/**
 * Standalone vendor bundle extractor (Laravel yüklenmeden çalışır).
 * GET ?key=gk-laravel-update-2026
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'gk-laravel-update-2026' && $key !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'forbidden']);
    exit;
}

@set_time_limit(600);
@ini_set('max_execution_time', '600');
@ini_set('memory_limit', '512M');

$base = __DIR__;
if (! is_dir($base.'/app') && is_dir(dirname($base).'/app')) {
    $base = dirname($base);
}

$candidates = [
    $base.'/storage/app/laravel-vendor.zip',
    $base.'/storage/app/laravel-vendor.tgz',
    $base.'/laravel-vendor.zip',
];

$bundle = null;
foreach ($candidates as $path) {
    if (is_file($path)) {
        $bundle = $path;
        break;
    }
}

if ($bundle === null) {
    echo json_encode(['ok' => false, 'message' => 'bundle missing', 'base' => $base]);
    exit;
}

$ok = false;
$error = null;

if (str_ends_with($bundle, '.zip') && class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    if ($zip->open($bundle) === true) {
        $ok = $zip->extractTo($base);
        $zip->close();
        if (! $ok) {
            $error = 'ZipArchive extractTo failed';
        }
    } else {
        $error = 'ZipArchive open failed';
    }
} else {
    try {
        $phar = new PharData($bundle);
        $phar->extractTo($base, null, true);
        $ok = is_dir($base.'/vendor/laravel/framework');
    } catch (Throwable $e) {
        $error = $e->getMessage();
        if (function_exists('shell_exec')) {
            $out = (string) @shell_exec('tar -xzf '.escapeshellarg($bundle).' -C '.escapeshellarg($base).' 2>&1');
            $ok = is_dir($base.'/vendor/laravel/framework');
            if (! $ok) {
                $error .= ' / '.$out;
            }
        }
    }
}

$version = null;
$versionFile = $base.'/vendor/laravel/framework/src/Illuminate/Foundation/Application.php';
if (is_readable($versionFile)) {
    $src = (string) file_get_contents($versionFile);
    if (preg_match("/const VERSION = '([^']+)'/", $src, $m)) {
        $version = $m[1];
    }
}

if ($ok) {
    @unlink($bundle);
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
}

echo json_encode([
    'ok' => $ok && $version !== null,
    'message' => $ok ? ('Vendor extracted · Laravel '.($version ?? '?')) : ('Extract failed: '.($error ?? 'unknown')),
    'version' => $version,
    'base' => $base,
    'bundle' => basename($bundle),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
