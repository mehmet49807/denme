#!/usr/bin/env python3
"""Generate patch to fix admin-nav partial (remove rapor, add GitHub after AI)."""

from __future__ import annotations

import base64
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-nav-partial.php"

github_link = ROOT / "admin-panel/resources/views/partials/admin-nav-github-link.blade.php"
github_b64 = base64.b64encode(github_link.read_bytes()).decode() if github_link.is_file() else ""

php = f"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {{
    http_response_code(403);
    exit('forbidden');
}}

$webRoot = __DIR__;
$adminRoot = dirname($webRoot).'/admin.gonulkoprusu.com';
if (! is_dir($adminRoot)) {{
    $adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
}}

$navFile = $adminRoot.'/resources/views/partials/admin-nav.blade.php';
if (! is_file($navFile)) {{
    exit("admin-nav missing\\n");
}}

$mode = (string) ($_GET['mode'] ?? 'fix');
$nav = file_get_contents($navFile);

if ($mode === 'dump') {{
    echo $nav;
    exit;
}}

$githubPartial = $adminRoot.'/resources/views/partials/admin-nav-github-link.blade.php';
if ('{github_b64}' !== '') {{
    @mkdir(dirname($githubPartial), 0755, true);
    file_put_contents($githubPartial, base64_decode('{github_b64}'));
    echo "wrote admin-nav-github-link.blade.php\\n";
}}

$include = "@include('partials.admin-nav-github-link')";
$newNav = $nav;

$newNav = preg_replace('/<a[^>]*admin\\.rapor[^>]*>[\\s\\S]*?<\\/a>\\s*/', '', $newNav) ?? $newNav;
$newNav = preg_replace('/<a[^>]*\\/rapor[^>]*>[\\s\\S]*?<\\/a>\\s*/', '', $newNav) ?? $newNav;
$newNav = preg_replace('/<a[^>]*>[^<]*AI Rapor[^<]*<\\/a>\\s*/', '', $newNav) ?? $newNav;
$newNav = preg_replace('/^.*admin\\.rapor.*$\\n?/m', '', $newNav) ?? $newNav;
$newNav = str_replace($include, '', $newNav);
$newNav = str_replace('@include("partials.admin-nav-github-link")', '', $newNav);

$aiMarker = "route('admin.ai')";
$pos = strpos($newNav, $aiMarker);
if ($pos !== false && ! str_contains(substr($newNav, $pos, 800), 'admin-nav-github-link') && ! str_contains($newNav, "route('admin.github')")) {{
    $close = strpos($newNav, '</a>', $pos);
    if ($close !== false) {{
        $insertAt = $close + 4;
        $newNav = substr($newNav, 0, $insertAt)."\\n        ".$include.substr($newNav, $insertAt);
        echo "inserted github after AI Denetim in admin-nav\\n";
    }}
}}

if ($newNav !== $nav) {{
    file_put_contents($navFile, $newNav);
    echo "patched partials/admin-nav.blade.php\\n";
}} else {{
    echo "admin-nav unchanged\\n";
}}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
$compile = @shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:cache 2>&1');
if (is_string($compile) && trim($compile) !== '') {{
    echo trim($compile)."\\n";
}}
echo "OK\\n";
"""

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
