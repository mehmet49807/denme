#!/usr/bin/env python3
"""Generate patch-web-login-feed.php — default authenticated users to Akış (feed)."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-login-feed.php"

files = {
    "app/Http/Controllers/Web/HomeController.php": ROOT
    / "web-site/app/Http/Controllers/Web/HomeController.php",
    "app/Http/Controllers/Web/AuthPageController.php": ROOT
    / "web-site/app/Http/Controllers/Web/AuthPageController.php",
    "resources/views/partials/logo.blade.php": ROOT
    / "web-site/resources/views/partials/logo.blade.php",
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

echo "Gonul Koprüsü — giriş sonrası Akış varsayılan patch\n";

foreach ($files as $rel => $b64) {
    $path = $webRoot.'/'.ltrim($rel, '/');
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

echo "done\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(f"wrote {OUT} ({OUT.stat().st_size} bytes)")
