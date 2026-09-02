<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/plain; charset=utf-8');
@set_time_limit(300);

$adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
$webRoot = '/home/gonulkop/public_html';

echo "Syncing missing files from web-site to admin panel...\n";

// Directories to fully sync (copy missing files only)
$syncDirs = [
    'app/Models',
    'app/Services',
    'app/Traits',
    'app/Jobs',
    'app/Http/Middleware',
    'app/Http/Controllers/Web',
    'app/Console',
    'database/migrations',
    'database/seeders',
];

$totalCopied = 0;

foreach ($syncDirs as $dir) {
    $src = $webRoot.'/'.$dir;
    $dst = $adminRoot.'/'.$dir;
    
    if (! is_dir($src)) {
        echo "skip (no source): $dir\n";
        continue;
    }
    
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    $copied = 0;
    foreach ($iter as $file) {
        if (! $file->isFile()) continue;
        $relPath = substr($file->getPathname(), strlen($src));
        $target = $dst.$relPath;
        
        if (! is_file($target)) {
            @mkdir(dirname($target), 0755, true);
            if (@copy($file->getPathname(), $target)) {
                $copied++;
                $totalCopied++;
            }
        }
    }
    
    if ($copied > 0) {
        echo "synced $dir: $copied files\n";
    } else {
        echo "ok $dir (all present)\n";
    }
}

// Also sync config files
$configSrc = $webRoot.'/config';
$configDst = $adminRoot.'/config';
if (is_dir($configSrc)) {
    foreach (glob($configSrc.'/*.php') as $file) {
        $target = $configDst.'/'.basename($file);
        if (! is_file($target)) {
            @copy($file, $target);
            echo "copied config/".basename($file)."\n";
            $totalCopied++;
        }
    }
}

// Also sync resources/views if missing
$viewsSrc = $webRoot.'/resources/views';
$viewsDst = $adminRoot.'/resources/views';
if (is_dir($viewsSrc)) {
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsSrc, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    $viewsCopied = 0;
    foreach ($iter as $file) {
        if (! $file->isFile()) continue;
        $relPath = substr($file->getPathname(), strlen($viewsSrc));
        $target = $viewsDst.$relPath;
        if (! is_file($target)) {
            @mkdir(dirname($target), 0755, true);
            if (@copy($file->getPathname(), $target)) {
                $viewsCopied++;
                $totalCopied++;
            }
        }
    }
    if ($viewsCopied > 0) {
        echo "synced resources/views: $viewsCopied files\n";
    }
}

// Clear cache
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan config:clear 2>/dev/null');
if (function_exists('opcache_reset')) {
    @opcache_reset();
}

echo "\nTotal files copied: $totalCopied\n";
echo "DONE\n";
