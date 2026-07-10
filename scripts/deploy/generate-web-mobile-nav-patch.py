#!/usr/bin/env python3
"""Generate patch-web-mobile-nav.php — smart mobile bottom nav to live web."""

from __future__ import annotations

import base64
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-mobile-nav.php"

files = {
    "css/mobile-bottom-nav.css": ROOT / "web-site/public/css/mobile-bottom-nav.css",
    "css/nav-icon-animations.css": ROOT / "web-site/public/css/nav-icon-animations.css",
    "js/mobile-bottom-nav.js": ROOT / "web-site/public/js/mobile-bottom-nav.js",
    "resources/views/partials/app-sidebar.blade.php": ROOT
    / "web-site/resources/views/partials/app-sidebar.blade.php",
    "resources/views/partials/sidebar-icon.blade.php": ROOT
    / "web-site/resources/views/partials/sidebar-icon.blade.php",
    "resources/views/layouts/app-with-sidebar.blade.php": ROOT
    / "web-site/resources/views/layouts/app-with-sidebar.blade.php",
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

echo "Gonul Koprüsü — mobil alt menü patch\n";

foreach ($files as $rel => $b64) {
    $path = $webRoot.'/'.ltrim($rel, '/');
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\n";
}

$layoutPath = $webRoot.'/resources/views/layouts/app.blade.php';
if (is_file($layoutPath)) {
    $layout = file_get_contents($layoutPath);
    $newLayout = $layout;
    $mobileLink = "<link rel=\"stylesheet\" href=\"{{ asset('css/mobile-bottom-nav.css') }}?v=mobile-bottom-nav-4\">";
    $navAnimLink = "<link rel=\"stylesheet\" href=\"{{ asset('css/nav-icon-animations.css') }}?v=nav-icon-animations-3\">";

    if (! str_contains($newLayout, 'mobile-bottom-nav.css')) {
        $needle = "@if(\$appShell)";
        if (str_contains($newLayout, $needle)) {
            $newLayout = str_replace(
                $needle,
                $needle."\n    ".$mobileLink."\n    ".$navAnimLink,
                $newLayout
            );
            echo "linked mobile nav css in app.blade.php\n";
        }
    } else {
        $newLayout = preg_replace(
            '/mobile-bottom-nav\.css\'\)\s*\}\}\?v=[^"\']+/',
            "mobile-bottom-nav.css') }}?v=mobile-bottom-nav-4",
            $newLayout
        ) ?? $newLayout;
        echo "bumped mobile-bottom-nav.css version\n";
    }

    if (! str_contains($newLayout, 'nav-icon-animations.css')) {
        $newLayout = str_replace(
            $mobileLink,
            $mobileLink."\n    ".$navAnimLink,
            $newLayout
        );
        echo "linked nav-icon-animations.css\n";
    }

    if ($newLayout !== $layout) {
        file_put_contents($layoutPath, $newLayout);
        echo "patched layouts/app.blade.php\n";
    }

    $jsTag = "<script src=\"{{ asset('js/mobile-bottom-nav.js') }}?v=mobile-bottom-nav-1\"></script>";
    if (! str_contains($newLayout, 'mobile-bottom-nav.js')) {
        if (str_contains($newLayout, "asset('js/locations.js')")) {
            $newLayout = str_replace(
                "<script src=\"{{ asset('js/locations.js') }}?v=world-locations-1\"></script>",
                "<script src=\"{{ asset('js/locations.js') }}?v=world-locations-1\"></script>\n        ".$jsTag,
                $newLayout
            );
            file_put_contents($layoutPath, $newLayout);
            echo "linked mobile-bottom-nav.js in app.blade.php\n";
        }
    }
}

foreach (['view:clear', 'route:clear', 'cache:clear'] as $command) {
    try {
        @shell_exec('cd '.escapeshellarg($webRoot).' && php artisan '.$command.' 2>/dev/null');
    } catch (Throwable $e) {
    }
}

echo "OK\n";
"""

OUT.write_text(php.replace("FILES_JSON", json.dumps(payload)), encoding="utf-8")
print(OUT, OUT.stat().st_size)
