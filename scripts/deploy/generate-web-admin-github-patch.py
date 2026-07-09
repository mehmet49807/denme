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
            "/Route::get\('\/seo',/",
            "Route::get('/github', [AdminGithubController::class, 'index'])->name('admin.github');\n    Route::post('/github/check', [AdminGithubController::class, 'check'])->name('admin.github.check');\n    Route::post('/github/clear-cache', [AdminGithubController::class, 'clearCache'])->name('admin.github.clear-cache');\n    Route::get('/seo',",
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
    if (! str_contains($newLayout, 'admin-nav-github-link') && ! str_contains($newLayout, "route('admin.github')")) {
        if (preg_match('/<nav[^>]*class="admin-sidebar-nav"[^>]*>/', $newLayout)) {
            $newLayout = preg_replace(
                '/(\s*)<\/nav>(\s*<div class="admin-sidebar-footer">)/',
                "$1    $include\n$1</nav>$2",
                $newLayout,
                1
            ) ?? $newLayout;
        } else {
            $newLayout = str_replace(
                '<div class="admin-sidebar-footer">',
                $include."\n            <div class=\"admin-sidebar-footer\">",
                $newLayout
            );
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
