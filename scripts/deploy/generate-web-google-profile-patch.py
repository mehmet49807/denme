#!/usr/bin/env python3
"""Generate patch-web-google-profile.php — Google OAuth + profile mobile toolbar fixes."""

from __future__ import annotations

import base64
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "patch-web-google-profile.php"


def q(value: str) -> str:
    return "'" + value.replace("\\", "\\\\").replace("'", "\\'") + "'"


READ_PATCH = (ROOT / "patch-web-admin-read-file.php").read_text(encoding="utf-8")
SERVICES = (ROOT / "web-site/config/services.php").read_text(encoding="utf-8")
CSS_FIX = (ROOT / "web-site/public/css/profile-toolbar-mobile.css").read_text(encoding="utf-8")
GOOGLE_CONTROLLER = (ROOT / "web-site/app/Http/Controllers/Web/GoogleAuthController.php").read_text(encoding="utf-8")
GOOGLE_COMPLETE = (ROOT / "web-site/resources/views/web/google-complete.blade.php").read_text(encoding="utf-8")
LANG_PARTIAL = (ROOT / "web-site/resources/views/partials/profile-language-dropdown.blade.php").read_text(encoding="utf-8")
MOBILE_NAV_CSS = (ROOT / "web-site/public/css/mobile-bottom-nav.css").read_text(encoding="utf-8")
APP_SIDEBAR = (ROOT / "web-site/resources/views/partials/app-sidebar.blade.php").read_text(encoding="utf-8")

files = {
    "patch-web-admin-read-file.php": READ_PATCH,
    "config/services.php": SERVICES,
    "app/Http/Controllers/Web/GoogleAuthController.php": GOOGLE_CONTROLLER,
    "resources/views/web/google-complete.blade.php": GOOGLE_COMPLETE,
    "resources/views/partials/profile-language-dropdown.blade.php": LANG_PARTIAL,
    "css/profile-toolbar-mobile.css": CSS_FIX,
    "css/mobile-bottom-nav.css": MOBILE_NAV_CSS,
    "resources/views/partials/app-sidebar.blade.php": APP_SIDEBAR,
}

files_json = ",\n".join(
    f"    {q(k)}: {q(base64.b64encode(v.encode()).decode())}" for k, v in files.items()
)

css_old = """@media (max-width: 520px) {
    .profile-toolbar-row { flex-wrap: wrap; }
    .profile-settings--toolbar { flex: 1 1 100%; }
    .profile-language-dropdown { margin-left: auto; }"""

css_new = """@media (max-width: 520px) {
    .profile-toolbar-row { flex-wrap: nowrap; align-items: center; gap: 0.45rem; margin-bottom: 0.85rem; }
    .profile-settings--toolbar { flex: 1 1 auto; min-width: 0; margin-bottom: 0; }
    .profile-language-dropdown { flex: 0 0 auto; margin-left: 0; }
    .profile-settings-toggle { width: 100%; max-width: 100%; font-size: 0.8rem; padding-left: 0.65rem; padding-right: 0.65rem; }
    .profile-settings-toggle-label { overflow: hidden; text-overflow: ellipsis; }"""

php = f"""<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {{
    http_response_code(403);
    exit('forbidden');
}}

$webRoot = __DIR__;
$files = [
{files_json}
];

echo "Gonul Koprüsü — Google OAuth + profil mobil düzen\\n";

foreach ($files as $rel => $b64) {{
    $path = $webRoot.'/'.ltrim($rel, '/');
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, base64_decode($b64));
    echo "write $rel ".filesize($path)."\\n";
}}

$cssPath = $webRoot.'/css/app.css';
if (is_file($cssPath)) {{
    $css = file_get_contents($cssPath);
    $old = {q(css_old)};
    $new = {q(css_new)};
    if (str_contains($css, $old)) {{
        file_put_contents($cssPath, str_replace($old, $new, $css));
        echo "patched css/app.css (mobile toolbar)\\n";
    }} elseif (str_contains($css, 'profile-toolbar-row')) {{
        echo "css/app.css already patched or layout differs\\n";
    }} else {{
        echo "css/app.css: profile-toolbar block not found\\n";
    }}
}} else {{
    echo "missing css/app.css\\n";
}}

$layoutCandidates = [
    $webRoot.'/resources/views/layouts/app-with-sidebar.blade.php',
    $webRoot.'/resources/views/layouts/app.blade.php',
];
$cssLink = '<link rel="stylesheet" href="{{ asset(\\'css/profile-toolbar-mobile.css\\') }}?v=profile-toolbar-mobile-1">';
foreach ($layoutCandidates as $layoutPath) {{
    if (! is_file($layoutPath)) {{
        continue;
    }}
    $layout = file_get_contents($layoutPath);
    if (str_contains($layout, 'profile-toolbar-mobile.css')) {{
        echo basename($layoutPath).": css link already present\\n";
        continue;
    }}
  if (str_contains($layout, "asset('css/app.css')")) {{
        $layout = str_replace(
            "@endif",
            "@endif\\n    <link rel=\\"stylesheet\\" href=\\"{{{{ asset('css/profile-toolbar-mobile.css') }}}}?v=profile-toolbar-mobile-1\\">",
            $layout,
            1
        );
        file_put_contents($layoutPath, $layout);
        echo "linked profile-toolbar-mobile.css in ".basename($layoutPath)."\\n";
    }}
}}

$envPath = $webRoot.'/.env';
$clientId = trim((string) ($_GET['client_id'] ?? ''));
$clientSecret = trim((string) ($_GET['client_secret'] ?? ''));
if ($clientId !== '' && $clientSecret !== '' && is_writable($envPath)) {{
    $env = is_file($envPath) ? file_get_contents($envPath) : '';
    $env = preg_replace('/^GOOGLE_CLIENT_ID=.*$/m', '', $env) ?? $env;
    $env = preg_replace('/^GOOGLE_CLIENT_SECRET=.*$/m', '', $env) ?? $env;
    $env = preg_replace('/^GOOGLE_REDIRECT_URI=.*$/m', '', $env) ?? $env;
    $env = rtrim($env)."\\n\\nGOOGLE_CLIENT_ID={{$clientId}}\\nGOOGLE_CLIENT_SECRET={{$clientSecret}}\\nGOOGLE_REDIRECT_URI=https://gonulkoprusu.com/auth/google/callback\\n";
    file_put_contents($envPath, $env);
    echo "updated .env google oauth\\n";
}} elseif ($clientId !== '' || $clientSecret !== '') {{
    echo ".env not writable or credentials incomplete\\n";
}} else {{
    echo "google oauth .env unchanged (pass client_id & client_secret to set)\\n";
}}

echo "\\nOK\\n";
"""

OUT.write_text(php, encoding="utf-8")
print(OUT, OUT.stat().st_size)
