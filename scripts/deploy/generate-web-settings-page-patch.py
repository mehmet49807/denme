#!/usr/bin/env python3
"""Generate patch-web-settings-page.php — Ayarlar menü + alt sayfalar."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-settings-page.php"

WEB = ROOT / "web-site"

files = {
    "app/Http/Controllers/Web/SettingsPageController.php": WEB
    / "app/Http/Controllers/Web/SettingsPageController.php",
    "app/Http/Controllers/Web/ProfilePageController.php": WEB
    / "app/Http/Controllers/Web/ProfilePageController.php",
    "resources/views/partials/profile-settings-menu.blade.php": WEB
    / "resources/views/partials/profile-settings-menu.blade.php",
    "resources/views/partials/profile-settings-sheet.blade.php": WEB
    / "resources/views/partials/profile-settings-sheet.blade.php",
    "resources/views/partials/profile-settings-open-btn.blade.php": WEB
    / "resources/views/partials/profile-settings-open-btn.blade.php",
    "resources/views/partials/settings-page-header.blade.php": WEB
    / "resources/views/partials/settings-page-header.blade.php",
    "resources/views/web/settings/profile.blade.php": WEB
    / "resources/views/web/settings/profile.blade.php",
    "resources/views/web/settings/hobbies.blade.php": WEB
    / "resources/views/web/settings/hobbies.blade.php",
    "resources/views/web/settings/language.blade.php": WEB
    / "resources/views/web/settings/language.blade.php",
    "resources/views/web/settings/password.blade.php": WEB
    / "resources/views/web/settings/password.blade.php",
    "resources/views/layouts/app.blade.php": WEB / "resources/views/layouts/app.blade.php",
    "public/css/profile-settings.css": WEB / "public/css/profile-settings.css",
    "public/js/profile-settings.js": WEB / "public/js/profile-settings.js",
    "routes/web.php": WEB / "routes/web.php",
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

echo "Gonul Koprüsü — ayarlar menü + alt sayfalar patch\n";

foreach ($files as $rel => $b64) {
    $path = $webRoot.'/'.ltrim($rel, '/');
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

foreach ([
    'resources/views/web/settings.blade.php',
    'resources/views/partials/profile-settings-panels.blade.php',
] as $obsolete) {
    $path = $webRoot.'/'.$obsolete;
    if (is_file($path)) {
        unlink($path);
        echo "removed $obsolete\n";
    }
}

echo "done\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(f"wrote {OUT} ({OUT.stat().st_size} bytes)")
