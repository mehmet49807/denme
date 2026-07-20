#!/usr/bin/env python3
"""Safely probe CloudLinux alt-php83 extensions without switching the whole site."""
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
probe = (ROOT / "scripts/deploy/assets/php83-probe.php").read_bytes()
enable = (ROOT / "scripts/deploy/assets/php83-enable.php").read_bytes()

INI_VARIANTS = {
    "abs_lib64": "\n".join(
        [
            "extension_dir=/opt/alt/php83/usr/lib64/php/modules",
            "extension=mbstring.so",
            "extension=pdo.so",
            "extension=pdo_mysql.so",
            "extension=fileinfo.so",
            "extension=zip.so",
            "extension=phar.so",
            "extension=intl.so",
            "extension=bcmath.so",
            "",
        ]
    ).encode(),
    "abs_full": "\n".join(
        [
            "extension=/opt/alt/php83/usr/lib64/php/modules/mbstring.so",
            "extension=/opt/alt/php83/usr/lib64/php/modules/pdo.so",
            "extension=/opt/alt/php83/usr/lib64/php/modules/pdo_mysql.so",
            "extension=/opt/alt/php83/usr/lib64/php/modules/fileinfo.so",
            "extension=/opt/alt/php83/usr/lib64/php/modules/zip.so",
            "extension=/opt/alt/php83/usr/lib64/php/modules/phar.so",
            "extension=/opt/alt/php83/usr/lib64/php/modules/intl.so",
            "extension=/opt/alt/php83/usr/lib64/php/modules/bcmath.so",
            "",
        ]
    ).encode(),
    "lib_path": "\n".join(
        [
            "extension_dir=/opt/alt/php83/usr/lib/php/modules",
            "extension=mbstring",
            "extension=pdo",
            "extension=pdo_mysql",
            "extension=fileinfo",
            "extension=zip",
            "extension=phar",
            "extension=intl",
            "extension=bcmath",
            "",
        ]
    ).encode(),
}

PROBE_BLOCK = "\n".join(
    [
        "",
        "# TEMP probe83 handler",
        '<FilesMatch "^(php83-probe|php83-enable)\\.php$">',
        "  <IfModule mime_module>",
        "    SetHandler application/x-httpd-alt-php83___lsphp",
        "  </IfModule>",
        "</FilesMatch>",
        "# END probe83",
        "",
    ]
)

BASES = {
    "web": (ROOT / "web-site/public/.htaccess").read_bytes(),
    "admin": (ROOT / "scripts/deploy/assets/admin.htaccess").read_bytes(),
}
URLS = {
    "web": "https://gonulkoprusu.com/php83-probe.php",
    "admin": "https://admin.gonulkoprusu.com/php83-probe.php",
}
ENABLE_URLS = {
    "web": "https://gonulkoprusu.com/php83-enable.php?key=gk-laravel-update-2026",
    "admin": "https://admin.gonulkoprusu.com/php83-enable.php?key=gk-laravel-update-2026",
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


def list_names(ftp) -> list[str]:
    names: list[str] = []
    try:
        ftp.retrlines("NLST", names.append)
    except Exception as e:
        print("NLST fail", e)
    return names


def ensure_probe_htaccess(base_htaccess: bytes) -> bytes:
    text = base_htaccess.decode("utf-8", "replace")
    text = re.sub(r"\n?# TEMP probe83 handler.*?# END probe83\n?", "\n", text, flags=re.S)
    text = re.sub(
        r"\n?# CloudLinux alt-php 8\.3.*?# php -- END cPanel-generated handler\n?",
        "\n",
        text,
        flags=re.S,
    )
    return (text.rstrip() + PROBE_BLOCK).encode("utf-8")


def fetch(url: str, retries: int = 4) -> str:
    last = ""
    for i in range(retries):
        try:
            req = urllib.request.Request(
                url,
                headers={
                    "User-Agent": "Mozilla/5.0 (compatible; GonulKoprusu-Probe/1.0)",
                    "Accept": "text/plain,*/*",
                },
            )
            last = urllib.request.urlopen(req, timeout=45).read().decode("utf-8", "replace")
            if "<!DOCTYPE html>" in last and "One moment" in last:
                time.sleep(3 + i)
                continue
            return last
        except Exception as e:
            last = f"ERROR {e}"
            time.sleep(2 + i)
    return last


def main() -> int:
    for target in ("web", "admin"):
        ftp = ftp_connect(target)
        try:
            print("====", target, "FTP PWD", ftp.pwd(), "====")
            names = list_names(ftp)
            print("NLST sample:", names[:40])
            for cmd in ("CWD ..", "CWD /", "CWD .."):
                try:
                    print(cmd, "->", ftp.sendcmd(cmd))
                    print("PWD", ftp.pwd(), "NLST", list_names(ftp)[:30])
                except Exception as e:
                    print(cmd, "fail", e)
            try:
                ftp.cwd("/")
            except Exception:
                pass

            ftp.storbinary("STOR php83-probe.php", BytesIO(probe))
            ftp.storbinary("STOR php83-enable.php", BytesIO(enable))
            ftp.storbinary("STOR .htaccess", BytesIO(ensure_probe_htaccess(BASES[target])))
        finally:
            ftp.quit()

    time.sleep(2)
    print("\n==== BASELINE PROBE (php 8.3) ====")
    for t, u in URLS.items():
        print("---", t, "---")
        print(fetch(u))

    print("\n==== FIX FROM INSIDE PHP 8.3 CageFS ====")
    # Must run under alt-php83 so writes hit the same CageFS view PHP 8.3 reads
    print(fetch(ENABLE_URLS["web"] + "&action=fix"))

    time.sleep(3)
    print("\n==== AFTER FIX ====")
    print(fetch(ENABLE_URLS["web"] + "&action=info"))
    for t, u in URLS.items():
        print("---", t, "probe2 ---")
        print(fetch(u))

    success = False
    bodies = {}
    for t, u in URLS.items():
        body = fetch(u)
        bodies[t] = body
        if "ext.mbstring=yes" in body and "ext.pdo=yes" in body and "PHP_VERSION=8.3" in body:
            print("SUCCESS_EXTENSIONS", t)
            success = True

    if not success:
        print("FIX_FAILED — leaving conf as-is for diagnosis; attempting soft restore")
        # Restore via PHP 8.3 view as well
        print(fetch(ENABLE_URLS["web"] + "&action=restore"))
        print("NO_INI_VARIANT_ENABLED_EXTENSIONS")
    return 0 if success else 2


if __name__ == "__main__":
    raise SystemExit(main())
