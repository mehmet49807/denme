<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

$root = __DIR__;
$viewsRoot = $root.'/resources/views';
$version = 'brand-v15';
$updated = [];

function gk_collect_php_files($dir, &$out)
{
    if (! is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir.'/'.$item;
        if (is_dir($path)) {
            gk_collect_php_files($path, $out);
            continue;
        }
        if (substr($item, -4) === '.php') {
            $out[] = $path;
        }
    }
}

$files = [];
gk_collect_php_files($viewsRoot, $files);

foreach ($files as $path) {
    $content = @file_get_contents($path);
    if ($content === false || strpos($content, 'logo') === false) {
        continue;
    }

    $new = $content;
    $new = str_replace('logo-mark.png?v=brand-v1', 'logo-mark.png?v='.$version, $new);
    $new = str_replace('logo-admin.png?v=brand-v1', 'logo-admin.png?v='.$version, $new);
    $new = str_replace('favicon.png?v=brand-v1', 'favicon.png?v='.$version, $new);
    $new = preg_replace('/logo-mark\.png\?v=brand-v\d+/', 'logo-mark.png?v='.$version, $new);
    $new = preg_replace('/logo-admin\.png\?v=brand-v\d+/', 'logo-admin.png?v='.$version, $new);

    if ($new !== $content) {
        file_put_contents($path, $new);
        $updated[] = str_replace($root.'/', '', $path);
    }
}

@shell_exec('cd '.escapeshellarg($root).' && php artisan view:clear 2>/dev/null');

foreach ($updated as $file) {
    echo $file."\n";
}

echo count($updated) ? "OK\n" : "NO_CHANGES\n";
