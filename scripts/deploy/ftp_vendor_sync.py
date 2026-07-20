#!/usr/bin/env python3
"""Upload composer.json/lock + vendor/ to web and/or admin FTP targets."""

from __future__ import annotations

import os
import sys
import ftplib
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path, PurePosixPath

ROOT = Path(__file__).resolve().parents[2]
MANIFEST = ROOT / "deploy" / "manifest.json"


def connect(host: str, user: str, password: str) -> ftplib.FTP:
    ftp = ftplib.FTP()
    ftp.connect(host, 21, timeout=90)
    ftp.login(user, password)
    ftp.set_pasv(True)
    return ftp


def ensure_dirs(ftp: ftplib.FTP, remote_file: str) -> None:
    parts = PurePosixPath(remote_file).parts[:-1]
    path = ""
    for part in parts:
        path = f"{path}/{part}" if path else part
        try:
            ftp.mkd(path)
        except Exception:
            pass


def upload_file(ftp: ftplib.FTP, local: Path, remote: str) -> None:
    ensure_dirs(ftp, remote)
    with local.open("rb") as fh:
        ftp.storbinary(f"STOR {remote}", fh)


def collect_vendor_files(vendor_dir: Path) -> list[tuple[Path, str]]:
    files: list[tuple[Path, str]] = []
    for path in vendor_dir.rglob("*"):
        if not path.is_file():
            continue
        # Skip bulky caches / tests noise where safe
        rel = path.relative_to(vendor_dir).as_posix()
        if "/tests/" in f"/{rel}/" or "/Tests/" in f"/{rel}/":
            continue
        files.append((path, f"vendor/{rel}"))
    return files


def upload_batch(host: str, user: str, password: str, batch: list[tuple[Path, str]]) -> int:
    ftp = connect(host, user, password)
    count = 0
    try:
        for local, remote in batch:
            upload_file(ftp, local, remote)
            count += 1
    finally:
        ftp.quit()
    return count


def deploy_vendor(target: str, build_dir: Path) -> None:
    import json

    manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
    host = manifest[target]["ftp"]["host"]
    user = os.environ[f"FTP_{target.upper()}_USER"]
    password = os.environ[f"FTP_{target.upper()}_PASSWORD"]
    workers = int(os.environ.get("FTP_PARALLEL", "6"))

    vendor_dir = build_dir / "vendor"
    if not vendor_dir.is_dir():
        raise SystemExit(f"vendor missing in {build_dir}")

    files = collect_vendor_files(vendor_dir)
    # composer meta first
    meta = []
    for name in ("composer.json", "composer.lock"):
        local = build_dir / name
        if local.is_file():
            meta.append((local, name))

    print(f"=== Vendor sync {target} -> {host} | files={len(files)} meta={len(meta)} ===")

    # Upload composer files on a single connection
    ftp = connect(host, user, password)
    try:
        for local, remote in meta:
            upload_file(ftp, local, remote)
            print(f"OK {remote}")
    finally:
        ftp.quit()

    if not files:
        print("NO_VENDOR_FILES")
        return

    chunk = max(1, (len(files) + workers - 1) // workers)
    batches = [files[i : i + chunk] for i in range(0, len(files), chunk)]
    uploaded = 0
    with ThreadPoolExecutor(max_workers=min(workers, len(batches))) as pool:
        futures = [pool.submit(upload_batch, host, user, password, batch) for batch in batches]
        for future in as_completed(futures):
            uploaded += future.result()
            print(f"PROGRESS {uploaded}/{len(files)}")

    print(f"VENDOR_DONE {target} uploaded={uploaded}")


def main() -> int:
    build = Path(os.environ.get("LARAVEL_BUILD_DIR", ROOT / "server" / "build")).resolve()
    args = [a for a in sys.argv[1:] if not a.startswith("-")]
    targets = args or ["web", "admin"]
    for target in targets:
        deploy_vendor(target, build)
    print("ALL_DONE")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
