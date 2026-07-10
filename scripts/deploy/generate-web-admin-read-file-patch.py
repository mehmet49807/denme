#!/usr/bin/env python3
"""Generate patch to read live admin files for diagnosis."""

from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-read-file.php"

php = r"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}
$root = (string) ($_GET['root'] ?? 'admin');
if ($root === 'web') {
    $base = __DIR__;
} else {
    $base = dirname(__DIR__).'/admin.gonulkoprusu.com';
    if (! is_dir($base)) {
        $base = '/home/gonulkop/admin.gonulkoprusu.com';
    }
}
$rel = (string) ($_GET['file'] ?? 'routes/adminlogin.php');
$path = $base.'/'.ltrim($rel, '/');
if (str_contains($rel, '..')) {
    exit("bad path\n");
}
if (! is_file($path)) {
    exit("missing $rel\n");
}
header('Content-Type: text/plain; charset=utf-8');
echo file_get_contents($path);
"""

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
