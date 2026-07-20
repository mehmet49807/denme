#!/usr/bin/env python3
"""Enable PHP 8.3 + extensions on cPanel (alt-php83) for Laravel 13."""
from __future__ import annotations

import json
import os
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


def fetch(url: str, retries: int = 10) -> str:
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


def clean_htaccess(raw: bytes) -> bytes:
    # Emergency restore without PHP 8.3 handler
    text = raw.decode("utf-8", "replace")
    # strip handler block if present for restore
    import re

    text = re.sub(
        r"\n?# CloudLinux alt-php 8\.3.*?# php -- END cPanel-generated handler\n?",
        "\n",
        text,
        flags=re.S,
    )
    return text.encode("utf-8")


def main() -> int:
    # Repo htaccess already includes alt-php83 handler (survives Deploy).
    for target in ("web", "admin"):
        ftp = ftp_connect(target)
        try:
            put(ftp, "php-version.php", probe_php)
            put(ftp, "php83-enable.php", enable_php)
            put(ftp, ".htaccess", BASES[target])
        finally:
            ftp.quit()

    time.sleep(4)
    body = fetch("https://gonulkoprusu.com/php-version.php")
    print("==== PRECHECK WEB ====")
    print(body)

    if "PHP_VERSION=8.3" in body and not extensions_ok(body):
        print("==== FIX CONF (php83) ====")
        fix = fetch("https://gonulkoprusu.com/php83-enable.php?key=gk-laravel-update-2026&action=fix")
        print(fix[:4000])
        if "write_alt_php=ok" not in fix and "mbstring.ini" not in fix:
            print("ENABLE_FIX_FAILED")
            # restore clean to keep site up on 8.2
            for t in ("web", "admin"):
                ftp = ftp_connect(t)
                try:
                    put(ftp, ".htaccess", clean_htaccess(BASES[t]))
                finally:
                    ftp.quit()
            return 1
        time.sleep(8)
        body = fetch("https://gonulkoprusu.com/php-version.php")
        print("==== POSTFIX ====")
        print(body)

    # Re-upload htaccess in case Deploy raced
    for target in ("web", "admin"):
        ftp = ftp_connect(target)
        try:
            put(ftp, ".htaccess", BASES[target])
            put(ftp, "php-version.php", probe_php)
        finally:
            ftp.quit()
    time.sleep(3)

    for target in ("web", "admin"):
        body = fetch(f"{URLS[target]}/php-version.php")
        print(f"==== {target} ====")
        print(body)
        if not extensions_ok(body):
            print(f"{target}: PHP 8.3 extensions missing")
            for t in ("web", "admin"):
                ftp = ftp_connect(t)
                try:
                    put(ftp, ".htaccess", clean_htaccess(BASES[t]))
                finally:
                    ftp.quit()
            return 2

    # Soft check homepage still up
    for target, path in (("web", "/"), ("admin", "/login")):
        try:
            code = urllib.request.urlopen(URLS[target] + path, timeout=30).getcode()
        except Exception as e:
            print(target, "HTTP fail", e)
            code = 0
        print(target, "http", code)
        if code not in (200, 301, 302):
            for t in ("web", "admin"):
                ftp = ftp_connect(t)
                try:
                    put(ftp, ".htaccess", clean_htaccess(BASES[t]))
                finally:
                    ftp.quit()
            return 3

    print("PHP_8_3_EXTENSIONS_OK")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
