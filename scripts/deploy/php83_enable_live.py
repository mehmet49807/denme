#!/usr/bin/env python3
"""Enable PHP 8.3 + extensions on cPanel (alt-php83) for Laravel 13."""
from __future__ import annotations

import json
import os
import re
import time
import urllib.request
from io import BytesIO
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
manifest = json.loads((ROOT / "deploy/manifest.json").read_text())
enable_php = (ROOT / "scripts/deploy/assets/php83-enable.php").read_bytes()
probe_php = (ROOT / "scripts/deploy/assets/php-version.php").read_bytes()

BASES = {
    "web": (ROOT / "web-site/public/.htaccess").read_bytes(),
    "admin": (ROOT / "scripts/deploy/assets/admin.htaccess").read_bytes(),
}
URLS = {
    "web": "https://gonulkoprusu.com",
    "admin": "https://admin.gonulkoprusu.com",
}

HANDLER_BLOCK = "\n".join(
    [
        "",
        "# CloudLinux alt-php 8.3 - Laravel 13",
        "# php -- BEGIN cPanel-generated handler",
        "<IfModule mime_module>",
        "  AddHandler application/x-httpd-alt-php83___lsphp .php .php8 .phtml",
        "</IfModule>",
        "# php -- END cPanel-generated handler",
        "",
    ]
)

TEMP_ENABLE_BLOCK = "\n".join(
    [
        "",
        "# TEMP php83-enable handler",
        '<Files "php83-enable.php">',
        "  <IfModule mime_module>",
        "    SetHandler application/x-httpd-alt-php83___lsphp",
        "  </IfModule>",
        "</Files>",
        "# END php83-enable",
        "",
    ]
)


def ftp_connect(target: str):
    import ftplib

    host = manifest[target]["ftp"]["host"]
    user = os.environ[f"FTP_{target.upper()}_USER"]
    password = os.environ[f"FTP_{target.upper()}_PASSWORD"]
    ftp = ftplib.FTP()
    ftp.connect(host, 21, timeout=90)
    ftp.login(user, password)
    ftp.set_pasv(True)
    return ftp


def fetch(url: str, retries: int = 8) -> str:
    last = ""
    for i in range(retries):
        try:
            req = urllib.request.Request(
                url,
                headers={
                    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
                    "Accept": "text/plain,text/html;q=0.9,*/*;q=0.8",
                },
            )
            last = urllib.request.urlopen(req, timeout=45).read().decode("utf-8", "replace")
            if "<!DOCTYPE html>" in last and ("One moment" in last or "throbber" in last):
                time.sleep(4 + i * 2)
                continue
            return last
        except Exception as e:
            last = f"ERROR {e}"
            time.sleep(2 + i)
    return last


def strip_handlers(text: str) -> str:
    text = re.sub(r"\n?# TEMP php83-enable handler.*?# END php83-enable\n?", "\n", text, flags=re.S)
    text = re.sub(r"\n?# TEMP probe83 handler.*?# END probe83\n?", "\n", text, flags=re.S)
    text = re.sub(
        r"\n?# CloudLinux alt-php 8\.3.*?# php -- END cPanel-generated handler\n?",
        "\n",
        text,
        flags=re.S,
    )
    text = re.sub(
        r"\n?# php -- BEGIN cPanel-generated handler.*?# php -- END cPanel-generated handler\n?",
        "\n",
        text,
        flags=re.S,
    )
    return text


def with_sitewide(raw: bytes) -> bytes:
    text = strip_handlers(raw.decode("utf-8", "replace"))
    return (text.rstrip() + HANDLER_BLOCK).encode("utf-8")


def with_temp_enable(raw: bytes) -> bytes:
    text = strip_handlers(raw.decode("utf-8", "replace"))
    return (text.rstrip() + TEMP_ENABLE_BLOCK).encode("utf-8")


def put(ftp, name: str, data: bytes) -> None:
    ftp.storbinary(f"STOR {name}", BytesIO(data))
    print("OK", name, len(data))


def extensions_ok(body: str) -> bool:
    return (
        "PHP_VERSION=8.3" in body
        and "ext.mbstring=yes" in body
        and "ext.pdo=yes" in body
        and "ext.fileinfo=yes" in body
    )


def main() -> int:
    # 1) Upload helpers + temporary PHP 8.3 handler for enable/probe files
    temp_block = "\n".join(
        [
            "",
            "# TEMP php83-enable handler",
            '<FilesMatch "^(php83-enable|php-version)\\.php$">',
            "  <IfModule mime_module>",
            "    SetHandler application/x-httpd-alt-php83___lsphp",
            "  </IfModule>",
            "</FilesMatch>",
            "# END php83-enable",
            "",
        ]
    )

    def with_temp(raw: bytes) -> bytes:
        text = strip_handlers(raw.decode("utf-8", "replace"))
        return (text.rstrip() + "\n" + temp_block).encode("utf-8")

    ftp = ftp_connect("web")
    try:
        put(ftp, "php83-enable.php", enable_php)
        put(ftp, "php-version.php", probe_php)
        put(ftp, ".htaccess", with_temp(BASES["web"]))
    finally:
        ftp.quit()

    time.sleep(3)
    pre = fetch("https://gonulkoprusu.com/php-version.php")
    print("==== PRECHECK ====")
    print(pre)

    if not extensions_ok(pre):
        fix = fetch("https://gonulkoprusu.com/php83-enable.php?key=gk-laravel-update-2026&action=fix")
        print("==== FIX ====")
        print(fix[:4000])
        if "write_alt_php=ok" not in fix and "mbstring.ini" not in fix:
            print("ENABLE_FIX_FAILED")
            return 1
        time.sleep(8)
        pre = fetch("https://gonulkoprusu.com/php-version.php")
        print("==== POSTFIX ====")
        print(pre)
        if not extensions_ok(pre):
            print("EXTENSIONS_STILL_MISSING_AFTER_FIX")
            return 1
    else:
        print("EXTENSIONS_ALREADY_OK")

    # 2) Switch both sites to PHP 8.3 site-wide
    for target in ("web", "admin"):
        ftp = ftp_connect(target)
        try:
            put(ftp, "php-version.php", probe_php)
            put(ftp, "php83-enable.php", enable_php)
            put(ftp, ".htaccess", with_sitewide(BASES[target]))
        finally:
            ftp.quit()

    time.sleep(3)

    # 3) Verify site-wide
    for target in ("web", "admin"):
        body = fetch(f"{URLS[target]}/php-version.php")
        print(f"==== {target} ====")
        print(body)
        if not extensions_ok(body):
            print(f"{target}: PHP 8.3 extensions missing")
            for t in ("web", "admin"):
                ftp = ftp_connect(t)
                try:
                    put(ftp, ".htaccess", BASES[t])
                finally:
                    ftp.quit()
            return 2

    print("PHP_8_3_EXTENSIONS_OK")
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
