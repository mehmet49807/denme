#!/usr/bin/env python3
"""Generate patch-web-admin-packages.php — Paketler + Uygulama + Pazarlama admin'e yazılır."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-packages.php"
ADMIN = ROOT / "admin-panel"

files = {
    "app/Models/SiteSetting.php": ADMIN / "app/Models/SiteSetting.php",
    "app/Services/SiteSettingsService.php": ADMIN / "app/Services/SiteSettingsService.php",
    "app/Services/PremiumPackagesService.php": ADMIN / "app/Services/PremiumPackagesService.php",
    "app/Http/Controllers/Admin/AdminPackagesController.php": ADMIN
    / "app/Http/Controllers/Admin/AdminPackagesController.php",
    "app/Http/Controllers/Admin/AdminAppLinksController.php": ADMIN
    / "app/Http/Controllers/Admin/AdminAppLinksController.php",
    "app/Http/Controllers/Admin/AdminMarketingController.php": ADMIN
    / "app/Http/Controllers/Admin/AdminMarketingController.php",
    "resources/views/admin/packages.blade.php": ADMIN
    / "resources/views/admin/packages.blade.php",
    "resources/views/admin/app-links.blade.php": ADMIN
    / "resources/views/admin/app-links.blade.php",
    "resources/views/admin/marketing.blade.php": ADMIN
    / "resources/views/admin/marketing.blade.php",
    "resources/views/partials/admin-nav.blade.php": ADMIN
    / "resources/views/partials/admin-nav.blade.php",
    "resources/views/layouts/admin.blade.php": ADMIN
    / "resources/views/layouts/admin.blade.php",
    "routes/adminlogin.php": ADMIN / "routes/adminlogin.php",
    "public/css/admin.css": ROOT / "scripts/deploy/assets/admin.css",
    "css/admin.css": ROOT / "scripts/deploy/assets/admin.css",
    "public/css/admin-lumiere.css": ROOT / "scripts/deploy/assets/admin-lumiere.css",
    "css/admin-lumiere.css": ROOT / "scripts/deploy/assets/admin-lumiere.css",
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

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

echo "Gonul Koprüsü — admin packages + app links patch\n";
echo "admin_root=$adminRoot\n";

foreach ($files as $rel => $b64) {
    $path = $adminRoot.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo $rel.' '.filesize($path)."\n";
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan cache:clear 2>/dev/null');
if (function_exists('opcache_reset')) {
    @opcache_reset();
}

echo "OK\n";
"""

php = php.replace("FILES_JSON", json.dumps(payload, ensure_ascii=False))
OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size, "files=", len(payload))
