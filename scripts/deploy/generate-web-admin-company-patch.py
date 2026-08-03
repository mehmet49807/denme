#!/usr/bin/env python3
"""Generate patch-web-admin-company.php — Şirket Bilgileri admin menü + controller + view."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-company.php"

admin_panel = ROOT / "admin-panel"

files = {
    "app/Http/Controllers/Admin/AdminCompanyController.php": admin_panel
    / "app/Http/Controllers/Admin/AdminCompanyController.php",
    "resources/views/admin/company.blade.php": admin_panel
    / "resources/views/admin/company.blade.php",
    "resources/views/partials/admin-nav.blade.php": admin_panel
    / "resources/views/partials/admin-nav.blade.php",
    "resources/views/partials/admin-icon.blade.php": admin_panel
    / "resources/views/partials/admin-icon.blade.php",
    "routes/adminlogin.php": admin_panel / "routes/adminlogin.php",
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

echo "admin_root=$adminRoot\n";

$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);

foreach ($files as $rel => $b64) {
    $path = $adminRoot.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)." bytes\n";
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan cache:clear 2>/dev/null');
echo "OK — Şirket Bilgileri menüsü eklendi\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(f"wrote {OUT} ({OUT.stat().st_size} bytes)")
