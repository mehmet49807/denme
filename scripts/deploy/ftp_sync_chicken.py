#!/usr/bin/env python3
"""Upload /workspace/chicken to chicken.gonulkoprusu.com FTP root."""

from __future__ import annotations

import os
import sys
from ftplib import FTP, error_perm
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
LOCAL = ROOT / "chicken"
REMOTE_ROOT = os.environ.get("CHICKEN_FTP_REMOTE", "/chicken.gonulkoprusu.com")
HOST = os.environ.get("CHICKEN_FTP_HOST", "ftp.gonulkoprusu.com")
USER = os.environ.get("CHICKEN_FTP_USER", "chicken@gonulkoprusu.com")
PASSWORD = os.environ.get("CHICKEN_FTP_PASSWORD", "")

SKIP = {".git", ".DS_Store", "Thumbs.db", "config.local.php"}


def ensure_dir(ftp: FTP, remote_dir: str) -> None:
    parts = [p for p in remote_dir.split("/") if p]
    path = ""
    for part in parts:
        path += "/" + part
        try:
            ftp.mkd(path)
        except error_perm:
            pass


def upload_file(ftp: FTP, local: Path, remote: str) -> None:
    ensure_dir(ftp, str(Path(remote).parent).replace("\\", "/"))
    with local.open("rb") as handle:
        ftp.storbinary(f"STOR {remote}", handle)


def main() -> int:
    if not PASSWORD:
        print("CHICKEN_FTP_PASSWORD is required", file=sys.stderr)
        return 1
    if not LOCAL.is_dir():
        print(f"Missing {LOCAL}", file=sys.stderr)
        return 1

    ftp = FTP()
    ftp.connect(HOST, 21, timeout=30)
    ftp.login(USER, PASSWORD)
    ftp.set_pasv(True)

    # Prefer provided remote root; fall back to account home
    try:
        ftp.cwd(REMOTE_ROOT)
        base = REMOTE_ROOT.rstrip("/")
    except error_perm:
        base = ftp.pwd().rstrip("/") or ""
        print(f"Remote root fallback: {base or '/'}")

    files = [
        p for p in LOCAL.rglob("*")
        if p.is_file() and p.name not in SKIP and "config.local.php" not in p.parts
    ]
    print(f"Uploading {len(files)} files to {base or '/'} ...")
    for local in files:
        rel = local.relative_to(LOCAL).as_posix()
        remote = f"{base}/{rel}" if base else f"/{rel}"
        upload_file(ftp, local, remote)
        print("OK", rel)

    # Write server config if DB password provided
    db_pass = os.environ.get("CHICKEN_DB_PASS", "")
    if db_pass:
        cfg = (
            "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            "    'app_url' => 'https://chicken.gonulkoprusu.com',\n"
            "    'db' => [\n"
            "        'host' => 'localhost',\n"
            "        'port' => 3306,\n"
            "        'name' => " + repr(os.environ.get("CHICKEN_DB_NAME", "gonulkop_chicken")) + ",\n"
            "        'user' => " + repr(os.environ.get("CHICKEN_DB_USER", "gonulkop_admin")) + ",\n"
            "        'pass' => " + repr(db_pass) + ",\n"
            "        'charset' => 'utf8mb4',\n"
            "    ],\n"
            "];\n"
        )
        from io import BytesIO

        remote_cfg = f"{base}/config/config.local.php" if base else "/config/config.local.php"
        ensure_dir(ftp, str(Path(remote_cfg).parent).replace("\\", "/"))
        ftp.storbinary(f"STOR {remote_cfg}", BytesIO(cfg.encode("utf-8")))
        print("OK config/config.local.php")

    ftp.quit()
    print("Chicken deploy finished")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
