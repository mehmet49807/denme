<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
if (! is_dir($adminRoot)) {
    $adminRoot = dirname(__DIR__).'/admin.gonulkoprusu.com';
}
echo "admin_root=$adminRoot\n";

// Find the web-site vendor
$candidates = [
    '/home/gonulkop/public_html/vendor',
    '/home/gonulkop/gonulkoprusu.com/vendor',
    dirname($adminRoot).'/public_html/vendor',
    dirname($adminRoot).'/gonulkoprusu.com/vendor',
];

$webVendor = null;
foreach ($candidates as $path) {
    if (is_dir($path.'/laravel/framework')) {
        $webVendor = $path;
        echo "found web vendor: $path\n";
        break;
    }
}

if ($webVendor === null) {
    // Try to find it by scanning
    $homeDir = dirname($adminRoot);
    echo "scanning $homeDir for vendor...\n";
    foreach (glob($homeDir.'/*', GLOB_ONLYDIR) as $dir) {
        if (is_dir($dir.'/vendor/laravel/framework')) {
            $webVendor = $dir.'/vendor';
            echo "found via scan: $webVendor\n";
            break;
        }
    }
}

if ($webVendor === null) {
    echo "ERROR: no web vendor found\n";
    exit(1);
}

$target = $adminRoot.'/vendor';

// Remove existing if it's a broken symlink
if (is_link($target)) {
    @unlink($target);
    echo "removed broken symlink\n";
}

if (! is_dir($target)) {
    // Try symlink first
    @symlink($webVendor, $target);
    if (is_file($target.'/autoload.php')) {
        echo "vendor symlinked OK\n";
    } else {
        // Try copy
        @unlink($target);
        echo "symlink failed, trying copy...\n";
        $output = @shell_exec('cp -a '.escapeshellarg($webVendor).' '.escapeshellarg($target).' 2>&1');
        if (is_file($target.'/autoload.php')) {
            echo "vendor copied OK\n";
        } else {
            echo "copy failed: ".substr($output ?? '', 0, 500)."\n";
        }
    }
} else {
    if (is_file($target.'/autoload.php')) {
        echo "vendor already exists\n";
    } else {
        @unlink($target);
        @symlink($webVendor, $target);
        if (is_file($target.'/autoload.php')) {
            echo "vendor re-symlinked OK\n";
        } else {
            echo "vendor exists but broken\n";
        }
    }
}

// Test if autoload works
if (is_file($target.'/autoload.php')) {
    echo "autoload.php exists: YES\n";
    // Try to clear cache
    @shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
    @shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
    @shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan config:clear 2>/dev/null');
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
    echo "cache cleared\n";
} else {
    echo "autoload.php: MISSING\n";
}

echo "DONE\n";
