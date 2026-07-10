#!/usr/bin/env python3
"""Generate patch to inspect and repair broken admin layout after nav patch."""

from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-fix-layout.php"

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

$layoutFile = $adminRoot.'/resources/views/layouts/admin.blade.php';
if (! is_file($layoutFile)) {
    exit("layout missing\n");
}

$layout = file_get_contents($layoutFile);
$include = "@include('partials.admin-nav-github-link')";
$mode = (string) ($_GET['mode'] ?? 'fix');

if ($mode === 'dump') {
    $needle = (string) ($_GET['q'] ?? 'admin-sidebar-nav');
    $pos = strpos($layout, $needle);
    if ($pos === false) {
        $pos = strpos($layout, "route('admin.ai')");
    }
    echo substr($layout, max(0, $pos - 200), 4000);
    exit;
}

$newLayout = $layout;

$newLayout = preg_replace('/<a[^>]*admin\.rapor[^>]*>.*?<\/a>\s*/s', '', $newLayout) ?? $newLayout;
$newLayout = preg_replace('/<a[^>]*\/rapor[^>]*>.*?<\/a>\s*/s', '', $newLayout) ?? $newLayout;
$newLayout = preg_replace('/<a[^>]*>[^<]*AI Rapor[^<]*<\/a>\s*/s', '', $newLayout) ?? $newLayout;
$newLayout = str_replace($include, '', $newLayout);
$newLayout = str_replace('@include("partials.admin-nav-github-link")', '', $newLayout);

$aiMarker = "route('admin.ai')";
$pos = strpos($newLayout, $aiMarker);
if ($pos !== false) {
    $close = strpos($newLayout, '</a>', $pos);
    if ($close !== false) {
        $insertAt = $close + 4;
        $newLayout = substr($newLayout, 0, $insertAt)."\n            ".$include.substr($newLayout, $insertAt);
        echo "inserted github after admin.ai link\n";
    }
}

$maintenanceInclude = "@include('partials.admin-nav-maintenance-link')";
$cacheFooterInclude = "@include('partials.admin-sidebar-cache-clear')";
$newLayout = str_replace($maintenanceInclude, '', $newLayout);
$newLayout = str_replace('@include("partials.admin-nav-maintenance-link")', '', $newLayout);
$newLayout = str_replace($cacheFooterInclude, '', $newLayout);
$newLayout = str_replace('@include("partials.admin-sidebar-cache-clear")', '', $newLayout);

if (! str_contains($newLayout, 'admin-nav-maintenance-link')) {
    if (str_contains($newLayout, $include)) {
        $newLayout = str_replace($include, $include."\n            ".$maintenanceInclude, $newLayout);
        echo "inserted maintenance after github link\n";
    }
}

if (! str_contains($newLayout, 'admin-sidebar-cache-clear')) {
    if (str_contains($newLayout, '<div class="admin-sidebar-footer">')) {
        $newLayout = str_replace(
            '<div class="admin-sidebar-footer">',
            '<div class="admin-sidebar-footer">'."\n            ".$cacheFooterInclude,
            $newLayout
        );
        echo "inserted cache buttons in sidebar footer\n";
    }
}

if ($newLayout === $layout) {
    echo "no layout changes needed\n";
} else {
    file_put_contents($layoutFile, $newLayout);
    echo "layout repaired\n";
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
$compile = @shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:cache 2>&1');
if (is_string($compile) && $compile !== '') {
    echo trim($compile)."\n";
}
echo "OK\n";
"""

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
