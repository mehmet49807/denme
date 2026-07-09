#!/usr/bin/env python3
"""Generate patch to restore live adminlogin.php routes from backup if present."""

from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-restore-routes.php"

php = r"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

$adminRoot = dirname(__DIR__).'/admin.gonulkoprusu.com';
if (! is_dir($adminRoot)) {
    $adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
}

$routeFile = $adminRoot.'/routes/adminlogin.php';
$bak = $routeFile.'.bak';
$restored = false;

foreach ([$bak, $routeFile.'.bak.auto', '/home/gonulkop/admin.gonulkoprusu.com.bak/routes/adminlogin.php'] as $candidate) {
    if (is_file($candidate) && filesize($candidate) > 8000) {
        copy($candidate, $routeFile);
        echo "restored from $candidate\n";
        $restored = true;
        break;
    }
}

if (! $restored && is_file($routeFile)) {
    if (! is_file($bak)) {
        copy($routeFile, $bak);
        echo "backup saved adminlogin.php.bak\n";
    }
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
            "/(\['route' => 'admin\.ai'.*?\n)/",
            "$0",
            $newRoutes
        );
        $newRoutes = preg_replace(
            "/Route::post\('\/ai\/users\/\{user\}\/scan'.*?\n/",
            "$0    Route::get('/github', [AdminGithubController::class, 'index'])->name('admin.github');\n    Route::post('/github/check', [AdminGithubController::class, 'check'])->name('admin.github.check');\n    Route::post('/github/clear-cache', [AdminGithubController::class, 'clearCache'])->name('admin.github.clear-cache');\n",
            $newRoutes,
            1
        ) ?? $newRoutes;
    }
  $newRoutes = preg_replace("/\s*Route::get\('\/rapor'.*?\n/", "\n", $newRoutes) ?? $newRoutes;
    $newRoutes = preg_replace("/\s*Route::post\('\/rapor\/generate'.*?\n/", "\n", $newRoutes) ?? $newRoutes;
    $newRoutes = preg_replace("/\s*Route::get\('\/rapor\/\{rapor\}'.*?\n/", "\n", $newRoutes) ?? $newRoutes;
    $newRoutes = str_replace("use App\\Http\\Controllers\\Admin\\AdminRaporController;\n", '', $newRoutes);
    if ($newRoutes !== $routes) {
        file_put_contents($routeFile, $newRoutes);
        echo "patched routes/adminlogin.php\n";
    }
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
echo "bytes=".filesize($routeFile)."\nOK\n";
"""

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
