#!/usr/bin/env python3
"""Generate patch-web-admin-github.php — deploy GitHub menu + controls to live admin."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-github.php"

files = {
    "app/Http/Controllers/Admin/AdminGithubController.php": ROOT
    / "admin-panel/app/Http/Controllers/Admin/AdminGithubController.php",
    "app/Services/DeployGithubService.php": ROOT
    / "admin-panel/app/Services/DeployGithubService.php",
    "config/deploy.php": ROOT / "admin-panel/config/deploy.php",
    "routes/adminlogin.php": ROOT / "admin-panel/routes/adminlogin.php",
    "resources/views/admin/github.blade.php": ROOT
    / "admin-panel/resources/views/admin/github.blade.php",
    "resources/views/partials/admin-nav-github-link.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-nav-github-link.blade.php",
    "routes/admin_subdomain.php": ROOT / "admin-panel/routes/admin_subdomain.php",
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

$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);

echo "admin_root=$adminRoot\n";

foreach ($files as $rel => $b64) {
    $path = $adminRoot.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo $rel.' '.filesize($path)."\n";
}

$delete = [
    'app/Http/Controllers/Admin/AdminRaporController.php',
    'app/Models/AiRapor.php',
    'app/Models/Rapor.php',
    'app/Services/RaporService.php',
    'app/Services/AiRaporService.php',
    'resources/views/admin/rapor.blade.php',
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
}

$routeFile = $adminRoot.'/routes/adminlogin.php';
if (is_file($routeFile)) {
    $routes = file_get_contents($routeFile);
    $newRoutes = $routes;
    if (! str_contains($newRoutes, 'AdminGithubController')) {
        $newRoutes = str_replace(
            'use App\Http\Controllers\Admin\AdminPanelController;',
            "use App\Http\Controllers\Admin\AdminPanelController;\nuse App\Http\Controllers\Admin\AdminGithubController;",
            $newRoutes
        );
    }
    if (! str_contains($newRoutes, "->name('admin.github')")) {
        $newRoutes = preg_replace(
            "/Route::post\('\/ai\/users\/\{user\}\/scan'.*?\n/",
            "$0    Route::get('/github', [AdminGithubController::class, 'index'])->name('admin.github');\n    Route::post('/github/check', [AdminGithubController::class, 'check'])->name('admin.github.check');\n    Route::post('/github/clear-cache', [AdminGithubController::class, 'clearCache'])->name('admin.github.clear-cache');\n",
            $newRoutes,
            1
        ) ?? $newRoutes;
    } elseif (! str_contains($newRoutes, "->name('admin.github.check')")) {
        $newRoutes = str_replace(
            "->name('admin.github');",
            "->name('admin.github');\n    Route::post('/github/check', [AdminGithubController::class, 'check'])->name('admin.github.check');\n    Route::post('/github/clear-cache', [AdminGithubController::class, 'clearCache'])->name('admin.github.clear-cache');",
            $newRoutes
        );
    }

    $newRoutes = preg_replace(
        "/Route::get\('\/rapor'.*?\n/",
        '',
        $newRoutes
    ) ?? $newRoutes;
    $newRoutes = preg_replace(
        "/Route::post\('\/rapor\/generate'.*?\n/",
        '',
        $newRoutes
    ) ?? $newRoutes;
    $newRoutes = preg_replace(
        "/Route::get\('\/rapor\/\{rapor\}'.*?\n/",
        '',
        $newRoutes
    ) ?? $newRoutes;
    $newRoutes = str_replace("use App\\Http\\Controllers\\Admin\\AdminRaporController;\n", '', $newRoutes);

    if ($newRoutes !== $routes) {
        file_put_contents($routeFile, $newRoutes);
        echo "patched routes/adminlogin.php\n";
    }
}

$layoutFile = $adminRoot.'/resources/views/layouts/admin.blade.php';
if (is_file($layoutFile)) {
    $layout = file_get_contents($layoutFile);
    $newLayout = $layout;
    $include = "@include('partials.admin-nav-github-link')";

    $newLayout = preg_replace('/<a[^>]*admin\.rapor[^>]*>[\s\S]*?<\/a>\s*/', '', $newLayout) ?? $newLayout;
    $newLayout = preg_replace('/<a[^>]*\/rapor[^>]*>[\s\S]*?<\/a>\s*/', '', $newLayout) ?? $newLayout;
    $newLayout = preg_replace('/<a[^>]*>[^<]*AI Rapor[^<]*<\/a>\s*/', '', $newLayout) ?? $newLayout;
    $newLayout = preg_replace('/^.*admin\.rapor.*$\n?/m', '', $newLayout) ?? $newLayout;
    $newLayout = str_replace("route('admin.rapor')", "route('admin.ai')", $newLayout);
    $newLayout = str_replace('route("admin.rapor")', 'route("admin.ai")', $newLayout);
    $newLayout = str_replace($include, '', $newLayout);
    $newLayout = str_replace('@include("partials.admin-nav-github-link")', '', $newLayout);

    $aiMarker = "route('admin.ai')";
    $pos = strpos($newLayout, $aiMarker);
    if ($pos !== false && ! str_contains(substr($newLayout, $pos, 800), 'admin-nav-github-link')) {
        $close = strpos($newLayout, '</a>', $pos);
        if ($close !== false) {
            $insertAt = $close + 4;
            $newLayout = substr($newLayout, 0, $insertAt)."\n            ".$include.substr($newLayout, $insertAt);
            echo "patched layout: github after AI Denetim\n";
        }
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
