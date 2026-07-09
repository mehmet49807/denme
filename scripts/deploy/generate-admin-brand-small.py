#!/usr/bin/env python3
from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
VERSION = "brand-v16"
files = {
    "css/admin.css": ROOT / "scripts/deploy/assets/admin.css",
    "css/admin-login-lumiere.css": ROOT / "scripts/deploy/assets/admin-login-lumiere.css",
}
enc = {k: base64.b64encode(v.read_bytes()).decode() for k, v in files.items()}

php_body = r"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}
$root = __DIR__;
$version = 'VERSION_PLACEHOLDER';
$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);
foreach ($files as $rel => $b64) {
    $path = $root.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo $rel.' '.filesize($path)."\n";
}
$updated = [];
$walk = function ($dir) use (&$walk, &$updated, $root, $version) {
    if (! is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir.'/'.$item;
        if (is_dir($path)) {
            $walk($path);
            continue;
        }
        if (! str_ends_with($item, '.php')) {
            continue;
        }
        $content = file_get_contents($path);
        if ($content === false || ! str_contains($content, 'logo')) {
            continue;
        }
        $new = $content;
        $new = preg_replace('/logo-mark\.png\?v=brand-v\d+/', 'logo-admin.png?v='.$version, $new);
        $new = preg_replace('/logo-admin\.png\?v=brand-v\d+/', 'logo-admin.png?v='.$version, $new);
        $new = preg_replace('/favicon\.png\?v=brand-v\d+/', 'favicon.png?v='.$version, $new);
        $new = str_replace('images/logo-mark.png', 'images/logo-admin.png', $new);
        if ($new !== $content) {
            file_put_contents($path, $new);
            $updated[] = str_replace($root.'/', '', $path);
        }
    }
};
$walk($root.'/resources/views');
foreach ($updated as $file) {
    echo 'view '.$file."\n";
}
@shell_exec('cd '.escapeshellarg($root).' && php artisan view:clear 2>/dev/null');
echo "OK\n";
"""

php = php_body.replace("VERSION_PLACEHOLDER", VERSION).replace("FILES_JSON", json.dumps(enc))
out = ROOT / "patch-admin-brand-small.php"
out.write_text(php, encoding="utf-8")
print(out, out.stat().st_size)
