#!/usr/bin/env python3
"""Deploy admin-nav partial with GitHub after AI Denetim."""

from __future__ import annotations

import base64
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-nav-partial.php"

nav = ROOT / "admin-panel/resources/views/partials/admin-nav.blade.php"
github = ROOT / "admin-panel/resources/views/partials/admin-nav-github-link.blade.php"

payload = {
    "resources/views/partials/admin-nav.blade.php": base64.b64encode(nav.read_bytes()).decode(),
    "resources/views/partials/admin-nav-github-link.blade.php": base64.b64encode(github.read_bytes()).decode(),
}

import json

php = r"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

$adminRoot = dirname(__DIR__).'/admin.gonulkoprusu.com';
if (! is_dir($adminRoot)) {
    $adminRoot = '/home/gonulkop/admin.gonulkoprusu.com';
}

$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);

foreach ($files as $rel => $b64) {
    $path = $adminRoot.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan route:clear 2>/dev/null');
echo "OK\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(OUT, OUT.stat().st_size)
