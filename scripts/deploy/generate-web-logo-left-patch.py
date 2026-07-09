#!/usr/bin/env python3
"""Regenerate patch-web-logo-left.php with web + helper deploy files."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-logo-left.php"

web_files = {
    "resources/views/partials/logo-brand-css.blade.php": ROOT
    / "web-site/resources/views/partials/logo-brand-css.blade.php",
    "resources/views/partials/logo.blade.php": ROOT
    / "web-site/resources/views/partials/logo.blade.php",
    "images/logo-mark.png": ROOT / "web-site/public/images/logo-mark.png",
    "images/logo-admin.png": ROOT / "web-site/public/images/logo-admin.png",
    "patch-web-admin-views.php": ROOT / "patch-web-admin-views.php",
    "patch-web-admin-github.php": ROOT / "patch-web-admin-github.php",
    "patch-web-admin-nav.php": ROOT / "patch-web-admin-nav.php",
}

payload = {
    rel: base64.b64encode(path.read_bytes()).decode()
    for rel, path in web_files.items()
    if path.is_file()
}

php = """<?php
if(($_GET['key']??'')!=='gk-cpanel-setup-2026'){http_response_code(403);exit('forbidden');}
$root=__DIR__;
$files=json_decode(<<<'JSON'
FILES_JSON
JSON,true);
foreach($files as $r=>$b){$p=$root.'/'.$r;@mkdir(dirname($p),0755,true);file_put_contents($p,base64_decode($b));echo $r.' '.filesize($p)."\\n";}
@shell_exec('cd '.escapeshellarg($root).' && php artisan view:clear 2>/dev/null');
echo "OK\\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(OUT, OUT.stat().st_size)
