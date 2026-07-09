#!/usr/bin/env python3
"""Deploy web-site/ and admin-panel/ trees to cPanel FTP targets."""

from __future__ import annotations

import json
import os
import sys
import time
import urllib.request
from ftplib import FTP, error_perm
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
MANIFEST = ROOT / "deploy" / "manifest.json"
SKIP_NAMES = {".git", ".DS_Store", "Thumbs.db"}


def load_manifest() -> dict:
    return json.loads(MANIFEST.read_text(encoding="utf-8"))


def iter_files(local_path: Path) -> list[Path]:
    if local_path.is_file():
        return [local_path]
    files: list[Path] = []
    for path in sorted(local_path.rglob("*")):
        if path.is_file() and path.name not in SKIP_NAMES:
            files.append(path)
    return files


def ensure_remote_dir(ftp: FTP, remote_dir: str) -> None:
    parts = [part for part in remote_dir.split("/") if part]
    current = ""
    for part in parts:
        current = f"{current}/{part}" if current else part
        try:
            ftp.mkd(current)
        except error_perm:
            pass


def upload_file(ftp: FTP, local_file: Path, remote_file: str, retries: int = 5) -> None:
    remote_dir = os.path.dirname(remote_file.replace("\\", "/"))
    if remote_dir:
        ensure_remote_dir(ftp, remote_dir)

    for attempt in range(1, retries + 1):
        try:
            with local_file.open("rb") as handle:
                ftp.storbinary(f"STOR {remote_file}", handle)
            size = ftp.size(remote_file)
            expected = local_file.stat().st_size
            if size != expected:
                raise RuntimeError(f"size mismatch for {remote_file}: {size} != {expected}")
            print(f"OK {remote_file} ({size} bytes)")
            return
        except Exception as exc:  # noqa: BLE001
            print(f"retry {attempt}/{retries} {remote_file}: {exc}", file=sys.stderr)
            time.sleep(min(attempt * 2, 10))
    raise RuntimeError(f"failed to upload {remote_file}")


def deploy_target(target: str, manifest: dict) -> None:
    cfg = manifest[target]
    user = os.environ[f"FTP_{target.upper()}_USER"]
    password = os.environ[f"FTP_{target.upper()}_PASSWORD"]
    host = cfg["ftp"]["host"]

    ftp = FTP(host, timeout=120)
    ftp.login(user, password)
    ftp.set_pasv(True)

    print(f"=== Deploy {target} -> {host} ({user}) ===")
    for mapping in cfg["mappings"]:
        local = ROOT / mapping["local"]
        remote_base = mapping["remote"].strip("/")

        if mapping.get("file"):
            if not local.is_file():
                print(f"SKIP missing file {local}")
                continue
            upload_file(ftp, local, remote_base)
            continue

        if not local.is_dir():
            print(f"SKIP missing dir {local}")
            continue

        for file_path in iter_files(local):
            rel = file_path.relative_to(local).as_posix()
            remote = f"{remote_base}/{rel}" if remote_base else rel
            upload_file(ftp, file_path, remote)

    ftp.quit()

    setup_key = os.environ.get("SETUP_CACHE_KEY", "")
    github_sha = os.environ.get("GITHUB_SHA", "")
    for url_template in cfg.get("post_deploy", []):
        if not setup_key:
            print(f"SKIP post-deploy (SETUP_CACHE_KEY missing): {url_template}")
            continue
        url = (
            url_template.replace("{SETUP_KEY}", setup_key).replace("{GITHUB_SHA}", github_sha)
        )
        try:
            with urllib.request.urlopen(url, timeout=30) as response:  # noqa: S310
                body = response.read(200).decode("utf-8", errors="replace")
                print(f"POST-DEPLOY {url} -> {body.strip()[:120]}")
        except Exception as exc:  # noqa: BLE001
            print(f"WARN post-deploy failed {url}: {exc}", file=sys.stderr)


def main() -> int:
    if not MANIFEST.is_file():
        print(f"manifest not found: {MANIFEST}", file=sys.stderr)
        return 1

    manifest = load_manifest()
    targets = sys.argv[1:] or ["web", "admin"]

    for target in targets:
        if target not in manifest:
            print(f"unknown target: {target}", file=sys.stderr)
            return 1
        required = [f"FTP_{target.upper()}_USER", f"FTP_{target.upper()}_PASSWORD"]
        missing = [name for name in required if not os.environ.get(name)]
        if missing:
            print(f"missing env for {target}: {', '.join(missing)}", file=sys.stderr)
            return 1
        deploy_target(target, manifest)

    print("DEPLOY_DONE")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
