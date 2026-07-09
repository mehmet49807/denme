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
$adminRoot = dirname(__DIR__).'/admin.gonulkoprusu.com';
if (! is_dir($adminRoot)) {
    $adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
}
$rel = (string) ($_GET['file'] ?? 'routes/adminlogin.php');
$path = $adminRoot.'/'.ltrim($rel, '/');
if (! is_file($path)) {
    exit("missing $rel\n");
}
header('Content-Type: text/plain; charset=utf-8');
echo file_get_contents($path);
"""

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
