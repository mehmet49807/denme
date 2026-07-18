<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit("forbidden\n");
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');

echo "diag2 start\n";
echo 'php='.PHP_VERSION."\n";
echo 'dir='.__DIR__."\n";

if (function_exists('opcache_reset')) {
    echo 'opcache_reset='.(@opcache_reset() ? '1' : '0')."\n";
}

$root = __DIR__;
foreach ([
    'app/Models/User.php',
    'app/Services/PremiumPackagesService.php',
    'app/Http/Controllers/Web/SetupController.php',
] as $rel) {
    $path = $root.'/'.$rel;
    $ok = is_file($path);
    echo ($ok ? 'ok' : 'missing').' '.$rel;
    if ($ok) {
        echo ' size='.filesize($path);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }
        $src = (string) file_get_contents($path);
        if (strpos($src, 'function packageBadge') !== false) {
            echo ' has=packageBadge';
        }
        if (strpos($src, 'FILE_IGNORE_NEW_LINES') !== false) {
            echo ' has=OLD_FILE_LOG';
        }
        if (strpos($src, 'opcache: sifirlandi') !== false || strpos($src, 'opcache: sifirlandi') !== false) {
            echo ' has=newDeploySync';
        }
        if (strpos($src, 'tail -n') !== false || strpos($src, 'stream_get_contents') !== false) {
            echo ' has=safeLog';
        }
    }
    echo "\n";
}

$log = $root.'/storage/logs/laravel.log';
if (is_file($log)) {
    echo "\n--- log tail ---\n";
    $size = (int) filesize($log);
    $fp = fopen($log, 'rb');
    if ($fp) {
        fseek($fp, max(0, $size - 12288));
        echo trim((string) stream_get_contents($fp))."\n";
        fclose($fp);
    }
}

echo "OK\n";
