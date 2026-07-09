#!/usr/bin/env python3
"""Generate patch-web-admin-logo.php — runs on web, writes to admin sibling dir."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-admin-logo.php"
VERSION = "brand-v16"
CSS_VERSION = "premium-v4"

files = {
    "images/logo-mark.png": ROOT / "web-site/public/images/logo-mark.png",
    "images/logo-admin.png": ROOT / "web-site/public/images/logo-admin.png",
    "images/favicon.png": ROOT / "web-site/public/images/favicon.png",
    "css/admin.css": ROOT / "scripts/deploy/assets/admin.css",
    "css/admin-login-lumiere.css": ROOT / "scripts/deploy/assets/admin-login-lumiere.css",
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

$version = 'VERSION_PLACEHOLDER';
$cssVersion = 'CSS_VERSION_PLACEHOLDER';
$files = json_decode(<<<'JSON'
FILES_JSON
JSON, true);

echo "admin_root=$adminRoot\n";

foreach ($files as $rel => $b64) {
    $path = $adminRoot.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo $rel.' '.filesize($path)."\n";
}

$updated = [];
$walk = function ($dir) use (&$walk, &$updated, $adminRoot, $version, $cssVersion) {
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
        if (! preg_match('/\.(blade\.php|php)$/i', $item)) {
            continue;
        }
        $content = file_get_contents($path);
        if ($content === false || (! str_contains($content, 'logo') && ! str_contains($content, 'premium-v') && ! str_contains($content, 'brand-v'))) {
            continue;
        }
        $new = $content;
        $new = preg_replace('/logo-mark\.png\?v=brand-v\d+/', 'logo-admin.png?v='.$version, $new);
        $new = preg_replace('/logo-admin\.png\?v=brand-v\d+/', 'logo-admin.png?v='.$version, $new);
        $new = preg_replace('/favicon\.png\?v=brand-v\d+/', 'favicon.png?v='.$version, $new);
        $new = preg_replace('/brand-v\d+/', $version, $new);
        $new = str_replace('images/logo-mark.png', 'images/logo-admin.png', $new);
        $new = preg_replace('/admin-login-lumiere\.css\?v=premium-v\d+/', 'admin-login-lumiere.css?v='.$cssVersion, $new);
        $new = preg_replace('/admin\.css\?v=[^"\']+/', 'admin.css?v='.$cssVersion, $new);
        $new = str_replace('premium-v3', $cssVersion, $new);
        if ($new !== $content) {
            file_put_contents($path, $new);
            $updated[] = str_replace($adminRoot.'/', '', $path);
        }
    }
};

foreach (['resources/views', 'config', 'app'] as $rel) {
    $walk($adminRoot.'/'.$rel);
}

foreach ($updated as $file) {
    echo 'view '.$file."\n";
}

@shell_exec('cd '.escapeshellarg($adminRoot).' && php artisan view:clear 2>/dev/null');
echo "OK\n";
"""

php = (
    php.replace("VERSION_PLACEHOLDER", VERSION)
    .replace("CSS_VERSION_PLACEHOLDER", CSS_VERSION)
    .replace("FILES_JSON", json.dumps(payload))
)

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
