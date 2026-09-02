<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
@set_time_limit(300);

$adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
$webRoot = '/home/gonulkop/public_html';

if (! is_dir($adminRoot) || ! is_dir($webRoot)) {
    http_response_code(500);
    exit("roots missing: admin=".is_dir($adminRoot)." web=".is_dir($webRoot)."\n");
}

echo "Copying missing app files from web-site to admin panel...\n";

// Files/dirs to copy from web-site that are missing on admin
$copyItems = [
    'app/Http/Controllers/Controller.php',
    'app/Http/Middleware/SecurityHeadersMiddleware.php',
    'app/Http/Middleware/ApplyUserLocale.php',
    'app/Http/Middleware/CaptureGrowthAttribution.php',
    'app/Http/Middleware/SetLocale.php',
    'app/Providers/AppServiceProvider.php',
    'app/Providers/RouteServiceProvider.php',
    'app/Providers/EventServiceProvider.php',
    'app/Http/Middleware/RequireSuperAdmin.php',
    'app/Exceptions/Handler.php',
    'app/Console/Commands',
    'app/Traits',
    'app/Services',
    'app/Support',
    'config/app.php',
    'config/auth.php',
    'config/broadcasting.php',
    'config/cache.php',
    'config/database.php',
    'config/filesystems.php',
    'config/logging.php',
    'config/mail.php',
    'config/queue.php',
    'config/sanctum.php',
    'config/session.php',
    'config/services.php',
    'config/view.php',
    'database/migrations',
    'database/seeders',
    'database/factories',
];

$copied = 0;
$skipped = 0;
$errors = 0;

foreach ($copyItems as $rel) {
    $src = $webRoot.'/'.$rel;
    $dst = $adminRoot.'/'.$rel;
    
    if (is_file($src)) {
        if (! is_file($dst)) {
            @mkdir(dirname($dst), 0755, true);
            if (@copy($src, $dst)) {
                echo "copied: $rel\n";
                $copied++;
            } else {
                echo "ERROR copy: $rel\n";
                $errors++;
            }
        } else {
            $skipped++;
        }
    } elseif (is_dir($src)) {
        if (! is_dir($dst)) {
            // Copy entire directory
            $output = @shell_exec('cp -r '.escapeshellarg($src).' '.escapeshellarg($dst).' 2>&1');
            if (is_dir($dst)) {
                $count = count(glob($dst.'/*') ?: []);
                echo "copied dir: $rel ($count files)\n";
                $copied++;
            } else {
                echo "ERROR dir: $rel - ".substr($output ?? '', 0, 200)."\n";
                $errors++;
            }
        } else {
            // Copy individual missing files
            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            $dirCopied = 0;
            foreach ($iter as $file) {
                $relPath = substr($file->getPathname(), strlen($src));
                $target = $dst.$relPath;
                if ($file->isFile() && ! is_file($target)) {
                    @mkdir(dirname($target), 0755, true);
                    if (@copy($file->getPathname(), $target)) {
                        $dirCopied++;
                    }
                }
            }
            if ($dirCopied > 0) {
                echo "merged dir: $rel ($dirCopied new files)\n";
                $copied += $dirCopied;
            } else {
                $skipped++;
            }
        }
    } else {
        echo "source missing: $rel\n";
    }
}

// Also copy entire app/Http/Controllers from web-site (excluding Admin/ which admin has)
$webControllers = $webRoot.'/app/Http/Controllers';
$adminControllers = $adminRoot.'/app/Http/Controllers';
if (is_dir($webControllers)) {
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($webControllers, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    $ctrlCopied = 0;
    foreach ($iter as $file) {
        if (!$file->isFile()) continue;
        $relPath = substr($file->getPathname(), strlen($webControllers));
        // Skip Admin/ directory (admin panel has its own)
        if (str_starts_with($relPath, '/Admin/')) continue;
        $target = $adminControllers.$relPath;
        if (! is_file($target)) {
            @mkdir(dirname($target), 0755, true);
            if (@copy($file->getPathname(), $target)) {
                $ctrlCopied++;
            }
        }
    }
    if ($ctrlCopied > 0) echo "copied $ctrlCopied web controllers\n";
}

// Also copy all app/Models from web-site
$webModels = $webRoot.'/app/Models';
$adminModels = $adminRoot.'/app/Models';
if (is_dir($webModels)) {
    foreach (glob($webModels.'/*.php') as $file) {
        $target = $adminModels.'/'.basename($file);
        if (! is_file($target)) {
            @copy($file, $target);
        }
    }
    echo "synced app/Models\n";
}

// Copy all config files
$configDir = $webRoot.'/config';
if (is_dir($configDir)) {
    foreach (glob($configDir.'/*.php') as $file) {
        $target = $adminRoot.'/config/'.basename($file);
        if (! is_file($target)) {
            @copy($file, $target);
        }
    }
    echo "synced config/\n";
}

// Create storage directories
$storageDirs = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/app/public',
    'storage/logs',
];
foreach ($storageDirs as $dir) {
    @mkdir($adminRoot.'/'.$dir, 0755, true);
}

// Clear cache
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan config:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan cache:clear 2>/dev/null');
if (function_exists('opcache_reset')) {
    @opcache_reset();
}

echo "\nSummary: copied=$copied skipped=$skipped errors=$errors\n";
echo "DONE\n";
