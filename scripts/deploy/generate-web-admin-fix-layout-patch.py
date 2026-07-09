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
    $pos = strpos($layout, "admin.ai");
    echo substr($layout, max(0, $pos - 400), 1600);
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

if ($newLayout === $layout) {
    echo "no layout changes needed\n";
} else {
    file_put_contents($layoutFile, $newLayout);
    echo "layout repaired\n";
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
echo "OK\n";
"""

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
