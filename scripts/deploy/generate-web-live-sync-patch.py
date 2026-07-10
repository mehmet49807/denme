#!/usr/bin/env python3
"""Generate patch-web-live-sync.php — tüm canlı web güncellemelerini tek seferde uygular."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-live-sync.php"

WEB = ROOT / "web-site"

files = {
    "css/mobile-bottom-nav.css": WEB / "public/css/mobile-bottom-nav.css",
    "css/nav-icon-animations.css": WEB / "public/css/nav-icon-animations.css",
    "css/profile-settings.css": WEB / "public/css/profile-settings.css",
    "js/mobile-bottom-nav.js": WEB / "public/js/mobile-bottom-nav.js",
    "js/profile-settings.js": WEB / "public/js/profile-settings.js",
    "resources/views/partials/app-sidebar.blade.php": WEB
    / "resources/views/partials/app-sidebar.blade.php",
    "resources/views/partials/sidebar-icon.blade.php": WEB
    / "resources/views/partials/sidebar-icon.blade.php",
    "resources/views/layouts/app-with-sidebar.blade.php": WEB
    / "resources/views/layouts/app-with-sidebar.blade.php",
    "resources/views/layouts/app.blade.php": WEB / "resources/views/layouts/app.blade.php",
    "resources/views/partials/logo.blade.php": WEB / "resources/views/partials/logo.blade.php",
    "resources/views/partials/profile-settings-open-btn.blade.php": WEB
    / "resources/views/partials/profile-settings-open-btn.blade.php",
    "resources/views/partials/profile-settings-panels.blade.php": WEB
    / "resources/views/partials/profile-settings-panels.blade.php",
    "resources/views/partials/profile-settings-sheet.blade.php": WEB
    / "resources/views/partials/profile-settings-sheet.blade.php",
    "resources/views/web/feed.blade.php": WEB / "resources/views/web/feed.blade.php",
    "resources/views/web/profile.blade.php": WEB / "resources/views/web/profile.blade.php",
    "resources/views/partials/profile-online-label.blade.php": WEB
    / "resources/views/partials/profile-online-label.blade.php",
    "css/feed-stories.css": WEB / "public/css/feed-stories.css",
    "css/profile-toolbar-mobile.css": WEB / "public/css/profile-toolbar-mobile.css",
    "css/feed-toolbar.css": WEB / "public/css/feed-toolbar.css",
    "resources/views/partials/feed-toolbar.blade.php": WEB
    / "resources/views/partials/feed-toolbar.blade.php",
    "resources/views/partials/theme-icon.blade.php": WEB
    / "resources/views/partials/theme-icon.blade.php",
    "app/Http/Controllers/Web/HomeController.php": WEB
    / "app/Http/Controllers/Web/HomeController.php",
    "app/Http/Controllers/Web/AuthPageController.php": WEB
    / "app/Http/Controllers/Web/AuthPageController.php",
    "app/Http/Controllers/Web/FeedPageController.php": WEB
    / "app/Http/Controllers/Web/FeedPageController.php",
    "app/Http/Controllers/Web/ProfilePageController.php": WEB
    / "app/Http/Controllers/Web/ProfilePageController.php",
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

echo "Gonul Koprüsü — canlı senkron patch\n";

foreach ($files as $rel => $b64) {
    $path = $webRoot.'/'.ltrim($rel, '/');
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

foreach ([
    'app/Http/Controllers/Web/SettingsPageController.php',
    'resources/views/partials/profile-settings-menu.blade.php',
    'resources/views/partials/settings-page-header.blade.php',
    'resources/views/web/settings/profile.blade.php',
    'resources/views/web/settings/hobbies.blade.php',
    'resources/views/web/settings/language.blade.php',
    'resources/views/web/settings/password.blade.php',
] as $obsolete) {
    $path = $webRoot.'/'.$obsolete;
    if (is_file($path)) {
        unlink($path);
        echo "removed $obsolete\n";
    }
}

foreach (['view:clear', 'route:clear', 'cache:clear', 'config:clear'] as $command) {
    try {
        @shell_exec('cd '.escapeshellarg($webRoot).' && php artisan '.$command.' 2>/dev/null');
    } catch (Throwable $e) {
    }
}

echo "OK\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(f"wrote {OUT} ({OUT.stat().st_size} bytes, {len(payload)} files)")
