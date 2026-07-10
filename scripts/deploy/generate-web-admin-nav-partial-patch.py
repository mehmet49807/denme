#!/usr/bin/env python3
"""Deploy admin-nav partials + inject Önbellek link and sidebar cache buttons into live layout."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-nav-partial.php"

files = {
    "resources/views/partials/admin-nav.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-nav.blade.php",
    "resources/views/partials/admin-nav-github-link.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-nav-github-link.blade.php",
    "resources/views/partials/admin-nav-maintenance-link.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-nav-maintenance-link.blade.php",
    "resources/views/partials/admin-cache-clear-buttons.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-cache-clear-buttons.blade.php",
    "resources/views/partials/admin-sidebar-cache-clear.blade.php": ROOT
    / "admin-panel/resources/views/partials/admin-sidebar-cache-clear.blade.php",
    "resources/views/admin/maintenance.blade.php": ROOT
    / "admin-panel/resources/views/admin/maintenance.blade.php",
    "app/Http/Controllers/Admin/AdminMaintenanceController.php": ROOT
    / "admin-panel/app/Http/Controllers/Admin/AdminMaintenanceController.php",
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

$adminRoot = dirname(__DIR__).'/admin.gonulkoprusu.com';
if (! is_dir($adminRoot)) {
    $adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
}

$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);

foreach ($files as $rel => $b64) {
    $path = $adminRoot.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

$layoutFile = $adminRoot.'/resources/views/layouts/admin.blade.php';
if (is_file($layoutFile)) {
    $layout = file_get_contents($layoutFile);
    $newLayout = $layout;
    $githubInclude = "@include('partials.admin-nav-github-link')";
    $maintenanceInclude = "@include('partials.admin-nav-maintenance-link')";
    $cacheFooterInclude = "@include('partials.admin-sidebar-cache-clear')";

    $newLayout = str_replace($maintenanceInclude, '', $newLayout);
    $newLayout = str_replace('@include("partials.admin-nav-maintenance-link")', '', $newLayout);
    $newLayout = str_replace($cacheFooterInclude, '', $newLayout);
    $newLayout = str_replace('@include("partials.admin-sidebar-cache-clear")', '', $newLayout);

    if (! str_contains($newLayout, 'admin-nav-maintenance-link')) {
        if (str_contains($newLayout, $githubInclude)) {
            $newLayout = str_replace(
                $githubInclude,
                $githubInclude."\n            ".$maintenanceInclude,
                $newLayout
            );
            echo "layout: maintenance after GitHub\n";
        } else {
            $aiMarker = "route('admin.ai')";
            $pos = strpos($newLayout, $aiMarker);
            if ($pos !== false) {
                $close = strpos($newLayout, '</a>', $pos);
                if ($close !== false) {
                    $insertAt = $close + 4;
                    $newLayout = substr($newLayout, 0, $insertAt)."\n            ".$maintenanceInclude.substr($newLayout, $insertAt);
                    echo "layout: maintenance after AI Denetim\n";
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
            echo "layout: cache buttons in sidebar footer\n";
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
                echo "layout: cache buttons before nav end\n";
            }
        }
    }

    if ($newLayout !== $layout) {
        file_put_contents($layoutFile, $newLayout);
        echo "patched layouts/admin.blade.php\n";
    }
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
echo "OK\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(OUT, OUT.stat().st_size)
