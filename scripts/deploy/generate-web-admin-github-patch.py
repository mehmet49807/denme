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
    "app/Http/Controllers/Admin/AdminMaintenanceController.php": ROOT
    / "admin-panel/app/Http/Controllers/Admin/AdminMaintenanceController.php",
    "app/Services/DeployGithubService.php": ROOT
    / "admin-panel/app/Services/DeployGithubService.php",
    "config/deploy.php": ROOT / "admin-panel/config/deploy.php",
    "resources/views/admin/github.blade.php": ROOT
    / "admin-panel/resources/views/admin/github.blade.php",
    "resources/views/admin/maintenance.blade.php": ROOT
    / "admin-panel/resources/views/admin/maintenance.blade.php",
    "resources/views/partials/admin-cache-clear-buttons.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-cache-clear-buttons.blade.php",
    "resources/views/partials/admin-cache-clear-buttons.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-cache-clear-buttons.blade.php",
    "resources/views/partials/admin-nav-maintenance-link.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-nav-maintenance-link.blade.php",
    "resources/views/partials/admin-sidebar-cache-clear.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-sidebar-cache-clear.blade.php",
    "resources/views/partials/admin-nav.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-nav.blade.php",
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
    if (! str_contains($newRoutes, 'AdminMaintenanceController')) {
        $newRoutes = str_replace(
            'use App\Http\Controllers\Admin\AdminGithubController;',
            "use App\Http\Controllers\Admin\AdminGithubController;\nuse App\Http\Controllers\Admin\AdminMaintenanceController;",
            $newRoutes
        );
        if (! str_contains($newRoutes, 'AdminMaintenanceController')) {
            $newRoutes = str_replace(
                'use App\Http\Controllers\Admin\AdminPanelController;',
                "use App\Http\Controllers\Admin\AdminPanelController;\nuse App\Http\Controllers\Admin\AdminMaintenanceController;",
                $newRoutes
            );
        }
    }
    if (! str_contains($newRoutes, "->name('admin.github')")) {
        $newRoutes = preg_replace(
            "/Route::post\('\/ai\/users\/\{user\}\/scan'.*?\n/",
            "$0    Route::get('/github', [AdminGithubController::class, 'index'])->name('admin.github');\n    Route::post('/github/check', [AdminGithubController::class, 'check'])->name('admin.github.check');\n    Route::post('/github/clear-cache', [AdminMaintenanceController::class, 'clearCache'])->name('admin.github.clear-cache');\n    Route::get('/maintenance', [AdminMaintenanceController::class, 'index'])->name('admin.maintenance');\n    Route::post('/maintenance/clear-cache', [AdminMaintenanceController::class, 'clearCache'])->name('admin.maintenance.clear-cache');\n",
            $newRoutes,
            1
        ) ?? $newRoutes;
    } elseif (! str_contains($newRoutes, "->name('admin.github.check')")) {
        $newRoutes = str_replace(
            "->name('admin.github');",
            "->name('admin.github');\n    Route::post('/github/check', [AdminGithubController::class, 'check'])->name('admin.github.check');\n    Route::post('/github/clear-cache', [AdminMaintenanceController::class, 'clearCache'])->name('admin.github.clear-cache');\n    Route::get('/maintenance', [AdminMaintenanceController::class, 'index'])->name('admin.maintenance');\n    Route::post('/maintenance/clear-cache', [AdminMaintenanceController::class, 'clearCache'])->name('admin.maintenance.clear-cache');",
            $newRoutes
        );
    } elseif (! str_contains($newRoutes, "->name('admin.maintenance')")) {
        $newRoutes = str_replace(
            "->name('admin.github.clear-cache');",
            "->name('admin.github.clear-cache');\n    Route::get('/maintenance', [AdminMaintenanceController::class, 'index'])->name('admin.maintenance');\n    Route::post('/maintenance/clear-cache', [AdminMaintenanceController::class, 'clearCache'])->name('admin.maintenance.clear-cache');",
            $newRoutes
        );
        $newRoutes = str_replace(
            '[AdminGithubController::class, \'clearCache\']',
            '[AdminMaintenanceController::class, \'clearCache\']',
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
    $githubInclude = "@include('partials.admin-nav-github-link')";
    $maintenanceInclude = "@include('partials.admin-nav-maintenance-link')";
    $cacheFooterInclude = "@include('partials.admin-sidebar-cache-clear')";

    $newLayout = preg_replace('/<a[^>]*admin\.rapor[^>]*>[\s\S]*?<\/a>\s*/', '', $newLayout) ?? $newLayout;
    $newLayout = preg_replace('/<a[^>]*\/rapor[^>]*>[\s\S]*?<\/a>\s*/', '', $newLayout) ?? $newLayout;
    $newLayout = preg_replace('/<a[^>]*>[^<]*AI Rapor[^<]*<\/a>\s*/', '', $newLayout) ?? $newLayout;
    $newLayout = preg_replace('/^.*admin\.rapor.*$\n?/m', '', $newLayout) ?? $newLayout;
    $newLayout = str_replace("route('admin.rapor')", "route('admin.ai')", $newLayout);
    $newLayout = str_replace('route("admin.rapor")', 'route("admin.ai")', $newLayout);
    $newLayout = str_replace($githubInclude, '', $newLayout);
    $newLayout = str_replace('@include("partials.admin-nav-github-link")', '', $newLayout);
    $newLayout = str_replace($maintenanceInclude, '', $newLayout);
    $newLayout = str_replace('@include("partials.admin-nav-maintenance-link")', '', $newLayout);
    $newLayout = str_replace($cacheFooterInclude, '', $newLayout);
    $newLayout = str_replace('@include("partials.admin-sidebar-cache-clear")', '', $newLayout);

    $aiMarker = "route('admin.ai')";
    $pos = strpos($newLayout, $aiMarker);
    if ($pos !== false && ! str_contains(substr($newLayout, $pos, 1200), 'admin-nav-github-link')) {
        $close = strpos($newLayout, '</a>', $pos);
        if ($close !== false) {
            $insertAt = $close + 4;
            $newLayout = substr($newLayout, 0, $insertAt)."\n            ".$githubInclude.substr($newLayout, $insertAt);
            echo "patched layout: github after AI Denetim\n";
        }
    }

    if (! str_contains($newLayout, 'admin-nav-maintenance-link')) {
        if (str_contains($newLayout, $githubInclude)) {
            $newLayout = str_replace(
                $githubInclude,
                $githubInclude."\n            ".$maintenanceInclude,
                $newLayout
            );
            echo "patched layout: maintenance after GitHub\n";
        } else {
            $pos = strpos($newLayout, $aiMarker);
            if ($pos !== false) {
                $close = strpos($newLayout, '</a>', $pos);
                if ($close !== false) {
                    $insertAt = $close + 4;
                    $newLayout = substr($newLayout, 0, $insertAt)."\n            ".$maintenanceInclude.substr($newLayout, $insertAt);
                    echo "patched layout: maintenance after AI Denetim\n";
                }
            }
        }
    }

    if (! str_contains($newLayout, 'admin-sidebar-cache-clear')) {
        if (str_contains($newLayout, '<div class="admin-sidebar-footer">')) {
            $newLayout = str_replace(
                '<div class="admin-sidebar-footer">',
                '<div class="admin-sidebar-footer">'."\n            ".$cacheFooterInclude,
                $newLayout
            );
            echo "patched layout: cache buttons in sidebar footer\n";
        } else {
            $replaced = preg_replace(
                '/(\s*)<\/nav>/',
                "$1    ".$cacheFooterInclude."\n$1</nav>",
                $newLayout,
                1,
                $count
            );
            if ($count > 0 && is_string($replaced)) {
                $newLayout = $replaced;
                echo "patched layout: cache buttons before nav end\n";
            }
        }
    }

    if (! str_contains($newLayout, "'admin.github'")) {
        $newLayout = str_replace(
            "'admin.ai' => 'ai',",
            "'admin.ai' => 'ai',\n        'admin.github' => 'seo',\n        'admin.maintenance' => 'violet',",
            $newLayout
        );
        echo "patched layout: admin.github theme\n";
    } elseif (! str_contains($newLayout, "'admin.maintenance'")) {
        $newLayout = str_replace(
            "'admin.github' => 'seo',",
            "'admin.github' => 'seo',\n        'admin.maintenance' => 'violet',",
            $newLayout
        );
        echo "patched layout: admin.maintenance theme\n";
    }

    if ($newLayout !== $layout) {
        file_put_contents($layoutFile, $newLayout);
        echo "patched layouts/admin.blade.php\n";
    }
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
$compile = @shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:cache 2>&1');
if (is_string($compile) && trim($compile) !== '') {
    echo trim($compile)."\n";
}
echo "OK\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(OUT, OUT.stat().st_size)
