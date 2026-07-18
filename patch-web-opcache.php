<?php
/**
 * Acil: LiteSpeed/PHP opcache sıfırla + laravel.log kuyruğu + kritik dosya kontrolü.
 * Feed 500 sonrası eski bytecode'un kalmasını engeller.
 */
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit("forbidden\n");
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');

$root = __DIR__;
$lines = ['Gonul Koprusu — opcache/diag', 'base='.$root, 'php='.PHP_VERSION, ''];

$reset = false;
if (function_exists('opcache_reset')) {
    $reset = @opcache_reset();
    $lines[] = 'opcache_reset: '.($reset ? 'ok' : 'fail');
} else {
    $lines[] = 'opcache_reset: yok';
}

foreach ([
    'app/Models/User.php',
    'app/Services/PremiumPackagesService.php',
    'app/Http/Controllers/Web/SetupController.php',
    'resources/views/partials/profile-member-badges.blade.php',
    'resources/views/web/feed.blade.php',
] as $rel) {
    $path = $root.'/'.$rel;
    if (! is_file($path)) {
        $lines[] = "missing $rel";
        continue;
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($path, true);
    }
    $src = (string) @file_get_contents($path);
    $markers = [];
    if (str_contains($src, 'function packageBadge')) {
        $markers[] = 'packageBadge';
    }
    if (str_contains($src, 'class PremiumPackagesService')) {
        $markers[] = 'PremiumPackagesService';
    }
    if (str_contains($src, 'tail -n')) {
        $markers[] = 'tail-log';
    }
    if (str_contains($src, 'FILE_IGNORE_NEW_LINES')) {
        $markers[] = 'OLD-file()-log';
    }
    $lines[] = sprintf(
        'file %s size=%d mtime=%s markers=%s',
        $rel,
        filesize($path),
        date('c', filemtime($path)),
        $markers ? implode(',', $markers) : '-'
    );
}

foreach (glob($root.'/storage/framework/views/*.php') ?: [] as $view) {
    @unlink($view);
}
$lines[] = 'compiled views cleared';

$autoload = $root.'/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
    try {
        $lines[] = 'PremiumPackagesService class_exists: '.(class_exists('App\\Services\\PremiumPackagesService') ? 'ok' : 'YOK');
    } catch (Throwable $e) {
        $lines[] = 'PremiumPackagesService class_exists HATA: '.$e->getMessage();
    }
} else {
    $lines[] = 'vendor/autoload.php yok';
}

$log = $root.'/storage/logs/laravel.log';
if (is_file($log)) {
    $lines[] = '';
    $lines[] = '--- laravel.log (tail 80) ---';
    $tail = @shell_exec('tail -n 80 '.escapeshellarg($log).' 2>/dev/null');
    if (is_string($tail) && trim($tail) !== '') {
        $lines[] = rtrim($tail);
    } else {
        // shell_exec kapalıysa son 32KB oku (tüm dosyayı yükleme)
        $size = filesize($log);
        $fp = @fopen($log, 'rb');
        if ($fp) {
            $seek = max(0, $size - 32768);
            fseek($fp, $seek);
            $chunk = stream_get_contents($fp);
            fclose($fp);
            $lines[] = trim((string) $chunk);
        } else {
            $lines[] = '(log okunamadi)';
        }
    }
}

$lines[] = '';
$lines[] = 'OK';
echo implode("\n", $lines)."\n";
