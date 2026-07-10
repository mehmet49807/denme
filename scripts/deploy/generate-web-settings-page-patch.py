#!/usr/bin/env python3
"""Generate patch-web-settings-page.php — Ayarlar tam sayfa (alt panel yerine)."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-settings-page.php"

files = {
    "app/Http/Controllers/Web/SettingsPageController.php": ROOT
    / "web-site/app/Http/Controllers/Web/SettingsPageController.php",
    "app/Http/Controllers/Web/ProfilePageController.php": ROOT
    / "web-site/app/Http/Controllers/Web/ProfilePageController.php",
    "resources/views/web/settings.blade.php": ROOT
    / "web-site/resources/views/web/settings.blade.php",
    "resources/views/partials/profile-settings-panels.blade.php": ROOT
    / "web-site/resources/views/partials/profile-settings-panels.blade.php",
    "resources/views/partials/profile-settings-open-btn.blade.php": ROOT
    / "web-site/resources/views/partials/profile-settings-open-btn.blade.php",
    "resources/views/layouts/app.blade.php": ROOT
    / "web-site/resources/views/layouts/app.blade.php",
    "public/css/profile-settings.css": ROOT / "web-site/public/css/profile-settings.css",
    "public/js/profile-settings.js": ROOT / "web-site/public/js/profile-settings.js",
    "routes/web.php": ROOT / "web-site/routes/web.php",
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
$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);

echo "Gonul Koprüsü — Ayarlar tam sayfa patch\n";

foreach ($files as $rel => $b64) {
    $path = $webRoot.'/'.ltrim($rel, '/');
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

$sheetPath = $webRoot.'/resources/views/partials/profile-settings-sheet.blade.php';
if (is_file($sheetPath)) {
    unlink($sheetPath);
    echo "removed profile-settings-sheet.blade.php\n";
}

echo "done\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(f"wrote {OUT} ({OUT.stat().st_size} bytes)")
