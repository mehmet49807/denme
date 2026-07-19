#!/usr/bin/env python3
"""Generate patch-admin-brand-live.php for the admin server."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-admin-brand-live.php"
VERSION = "brand-v17"

FILES = {
    "images/logo-mark.png": ROOT / "web-site/public/images/logo-mark.png",
    "images/logo-admin.png": ROOT / "web-site/public/images/logo-admin.png",
    "images/favicon.png": ROOT / "web-site/public/images/favicon.png",
    "css/admin.css": Path("/tmp/admin.css"),
    "css/admin-login-lumiere.css": Path("/tmp/admin-login-lumiere.css"),
}

payload = {
    rel: base64.b64encode(path.read_bytes()).decode()
    for rel, path in FILES.items()
    if path.is_file()
}

php = f"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {{
    http_response_code(403);
    exit('forbidden');
}}

$root = __DIR__;
$version = '{VERSION}';
$files = json_decode(<<<'JSON'
{json.dumps(payload)}
JSON, true);

foreach ($files as $rel => $b64) {{
    $path = $root.'/'.$rel;
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo $rel.' '.filesize($path)."\\n";
}}

$viewsRoot = $root.'/resources/views';
$updated = [];

$collect = function ($dir) use (&$collect, &$updated, $root, $version) {{
    if (! is_dir($dir)) {{
        return;
    }}
    foreach (scandir($dir) as $item) {{
        if ($item === '.' || $item === '..') {{
            continue;
        }}
        $path = $dir.'/'.$item;
        if (is_dir($path)) {{
            $collect($path);
            continue;
        }}
        if (! str_ends_with($item, '.php')) {{
            continue;
        }}
        $content = file_get_contents($path);
        if ($content === false || ! str_contains($content, 'logo')) {{
            continue;
        }}
        $new = $content;
        $new = preg_replace('/logo-mark\\.png\\?v=brand-v\\d+/', 'logo-admin.png?v='.$version, $new) ?? $new;
        $new = preg_replace('/logo-admin\\.png\\?v=brand-v\\d+/', 'logo-admin.png?v='.$version, $new) ?? $new;
        $new = preg_replace('/favicon\\.png\\?v=brand-v\\d+/', 'favicon.png?v='.$version, $new) ?? $new;
        $new = str_replace('logo-mark.png?v=brand-v1', 'logo-admin.png?v='.$version, $new);
        $new = str_replace('images/logo-mark.png', 'images/logo-admin.png', $new);
        if ($new !== $content) {{
            file_put_contents($path, $new);
            $updated[] = str_replace($root.'/', '', $path);
        }}
    }}
}};

$collect($viewsRoot);

foreach ($updated as $file) {{
    echo 'view '.$file."\\n";
}}

@shell_exec('cd '.escapeshellarg($root).' && php artisan view:clear 2>/dev/null');
echo count($updated) ? "OK\\n" : "OK\\n";
"""

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
