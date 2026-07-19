<?php
/**
 * Feed 500 acil yama — opcache temizle + badge/servis dosyalarını yaz.
 * Not: Tek satırlık dev JSON kullanma (FTP satır kırpınca json_decode patlar).
 */
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit("forbidden\n");
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');

$root = __DIR__;
$lines = ['Gonul Koprusu — feed 500 fix v2', 'base='.$root, ''];

if (function_exists('opcache_reset')) {
    @opcache_reset();
    $lines[] = 'opcache_reset: ok';
}

// Kritik dosyalar ayrı parça dosyalardan okunur (deploy ile birlikte gelir).
$partsDir = $root.'/.gk-feed-fix-parts';
// User.php buradan yazılmaz — canlı sync / FTP güncel modeli getirir.
// Eski parts User.php applyContentRanking'i silip /feed 500 üretiyordu.
$map = [
    'app/Services/PremiumPackagesService.php' => 'PremiumPackagesService.php',
    'resources/views/partials/profile-member-badges.blade.php' => 'profile-member-badges.blade.php',
    'app/Http/Controllers/Web/SetupController.php' => 'SetupController.php',
    'routes/web.php' => 'web.php',
];

$updated = 0;
foreach ($map as $rel => $partName) {
    $src = $partsDir.'/'.$partName;
    $dst = $root.'/'.$rel;
    if (! is_file($src)) {
        $lines[] = "skip missing part $partName";
        continue;
    }
    @mkdir(dirname($dst), 0755, true);
    $bytes = (int) filesize($src);
    if (@copy($src, $dst)) {
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($dst, true);
        }
        $lines[] = "write $rel $bytes";
        $updated++;
    } else {
        $lines[] = "FAIL write $rel";
    }
}

foreach (glob($root.'/storage/framework/views/*.php') ?: [] as $view) {
    @unlink($view);
}
$lines[] = 'compiled views cleared';
$lines[] = "updated_files=$updated";

$log = $root.'/storage/logs/laravel.log';
if (is_file($log)) {
    $lines[] = '';
    $lines[] = '--- laravel.log (tail 40) ---';
    $tail = @shell_exec('tail -n 40 '.escapeshellarg($log).' 2>/dev/null');
    if (is_string($tail) && trim($tail) !== '') {
        $lines[] = rtrim($tail);
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

$lines[] = '';
$lines[] = 'OK';
echo implode("\n", $lines)."\n";
