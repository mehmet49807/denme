#!/usr/bin/env python3
"""Upload a single vendor bundle archive to web/admin FTP."""

from __future__ import annotations

import json
import os
import sys
import ftplib
from pathlib import Path, PurePosixPath

ROOT = Path(__file__).resolve().parents[2]
MANIFEST = ROOT / "deploy" / "manifest.json"


def connect(host: str, user: str, password: str) -> ftplib.FTP:
    ftp = ftplib.FTP()
    ftp.connect(host, 21, timeout=120)
    ftp.login(user, password)
    ftp.set_pasv(True)
    try:
        ftp.voidcmd("TYPE I")
    except Exception:
        pass
    return ftp


def ensure_dir(ftp: ftplib.FTP, remote_dir: str) -> None:
    parts = PurePosixPath(remote_dir).parts
    path = ""
    for part in parts:
        path = f"{path}/{part}" if path else part
        try:
            ftp.mkd(path)
        except Exception:
            pass


def upload(ftp: ftplib.FTP, local: Path, remote: str) -> None:
    ensure_dir(ftp, str(PurePosixPath(remote).parent))
    with local.open("rb") as fh:
        ftp.storbinary(f"STOR {remote}", fh, blocksize=1024 * 256)


def main() -> int:
    bundle = Path(os.environ.get("LARAVEL_BUNDLE", ROOT / "server" / "build" / "laravel-vendor.tgz"))
    if not bundle.is_file():
        print(f"bundle missing: {bundle}", file=sys.stderr)
        return 1

    manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
    targets = [a for a in sys.argv[1:] if not a.startswith("-")] or ["web", "admin"]

    for target in targets:
        host = manifest[target]["ftp"]["host"]
        user = os.environ[f"FTP_{target.upper()}_USER"]
        password = os.environ[f"FTP_{target.upper()}_PASSWORD"]
        remote = "storage/app/laravel-vendor.tgz"
        print(f"=== Bundle {target} -> {host}:{remote} ({bundle.stat().st_size} bytes) ===")
        ftp = connect(host, user, password)
        try:
            upload(ftp, bundle, remote)
            print(f"OK {remote}")
        finally:
            ftp.quit()

    print("BUNDLE_DONE")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
