#!/usr/bin/env python3
"""Generate patch-web-admin-nav.php — remove AI Raporları, reposition GitHub menu."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-nav.php"

files = {
    "resources/views/admin/github.blade.php": ROOT
    / "admin-panel/resources/views/admin/github.blade.php",
    "resources/views/partials/admin-nav-github-link.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-nav-github-link.blade.php",
    "routes/adminlogin.php": ROOT / "admin-panel/routes/adminlogin.php",
    "app/Http/Controllers/Admin/AdminGithubController.php": ROOT
    / "admin-panel/app/Http/Controllers/Admin/AdminGithubController.php",
}

payload = {
    rel: base64.b64encode(path.read_bytes()).decode()
    for rel, path in files.items()
    if path.is_file()
}

php = r"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

$webRoot = __DIR__;
$adminRoot = dirname($webRoot).'/admin.gonulkoprusu.com';
if (! is_dir($adminRoot)) {
    $adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
}
if (! is_dir($adminRoot)) {
    http_response_code(500);
    exit("admin root missing\n");
}

echo "admin_root=$adminRoot\n";

$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);

foreach ($files as $rel => $b64) {
    $path = $adminRoot.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel\n";
}

$delete = [
    'app/Http/Controllers/Admin/AdminRaporController.php',
    'app/Models/AiRapor.php',
    'app/Models/Rapor.php',
    'app/Services/RaporService.php',
    'app/Services/AiRaporService.php',
    'resources/views/admin/rapor.blade.php',
    'resources/views/admin/rapor/index.blade.php',
    'resources/views/admin/rapor/show.blade.php',
    'resources/views/admin/rapor/generate.blade.php',
];
foreach ($delete as $rel) {
    $path = $adminRoot.'/'.$rel;
    if (is_file($path)) {
        unlink($path);
        echo "delete $rel\n";
    }
}
$raporDir = $adminRoot.'/resources/views/admin/rapor';
if (is_dir($raporDir)) {
    foreach (scandir($raporDir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $p = $raporDir.'/'.$item;
        if (is_file($p)) {
            unlink($p);
            echo "delete resources/views/admin/rapor/$item\n";
        }
    }
    @rmdir($raporDir);
    echo "rmdir resources/views/admin/rapor\n";
}

$layoutFile = $adminRoot.'/resources/views/layouts/admin.blade.php';
if (is_file($layoutFile)) {
    $layout = file_get_contents($layoutFile);
    $newLayout = $layout;
    $include = "@include('partials.admin-nav-github-link')";

    $newLayout = preg_replace(
        '/<a[^>]*admin\.rapor[^>]*>.*?<\/a>\s*/s',
        '',
        $newLayout
    ) ?? $newLayout;
    $newLayout = preg_replace(
        '/<a[^>]*\/rapor[^>]*>.*?<\/a>\s*/s',
        '',
        $newLayout
    ) ?? $newLayout;
    $newLayout = preg_replace(
        '/<a[^>]*>[^<]*AI Rapor[^<]*<\/a>\s*/s',
        '',
        $newLayout
    ) ?? $newLayout;

    $newLayout = str_replace($include, '', $newLayout);
    $newLayout = str_replace('@include("partials.admin-nav-github-link")', '', $newLayout);

    if (! preg_match("/route\('admin\.ai'\).*?admin-nav-github-link/s", $newLayout)) {
        $replaced = preg_replace(
            "/(<a[^>]*route\('admin\.ai'\)[^>]*>.*?<\/a>)/s",
            "$1\n            ".$include,
            $newLayout,
            1,
            $count
        );
        if ($count > 0 && is_string($replaced)) {
            $newLayout = $replaced;
            echo "patched layout: github after AI Denetim\n";
        } elseif (! str_contains($newLayout, 'admin-nav-github-link')) {
            $replaced = preg_replace(
                '/(\s*)<\/nav>(\s*<div class="admin-sidebar-footer">)/',
                "$1    ".$include."\n$1</nav>$2",
                $newLayout,
                1,
                $count
            );
            if ($count > 0 && is_string($replaced)) {
                $newLayout = $replaced;
                echo "patched layout: github before nav end\n";
            }
        }
    } else {
        echo "layout: github already after AI Denetim\n";
    }

    if ($newLayout !== $layout) {
        file_put_contents($layoutFile, $newLayout);
        echo "patched layouts/admin.blade.php\n";
    }
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
echo "OK\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(OUT, OUT.stat().st_size)
