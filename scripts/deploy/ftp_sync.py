#!/usr/bin/env python3
"""Deploy web-site/ and admin-panel/ trees to cPanel FTP targets (delta + parallel)."""

from __future__ import annotations

import hashlib
import json
import os
import sys
import time
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed
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


def file_hash(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def load_changed_paths() -> set[str] | None:
    if os.environ.get("DEPLOY_FULL_SYNC", "").strip() in {"1", "true", "yes"}:
        return None

    raw = os.environ.get("DEPLOY_CHANGED_FILES", "").strip()
    if not raw:
        return None

    path = Path(raw)
    if path.is_file():
        return {line.strip() for line in path.read_text(encoding="utf-8").splitlines() if line.strip()}

    return {part.strip() for part in raw.split(",") if part.strip()}


def path_matches_delta(repo_rel: str, changed: set[str]) -> bool:
    repo_rel = repo_rel.replace("\\", "/").lstrip("./")
    for item in changed:
        item = item.replace("\\", "/").lstrip("./")
        if repo_rel == item:
            return True
        if repo_rel.startswith(item.rstrip("/") + "/"):
            return True
        if item.startswith(repo_rel.rstrip("/") + "/"):
            return True
    return False


def should_upload(
    local_file: Path,
    remote_file: str,
    ftp: FTP,
    changed: set[str] | None,
    *,
    verify_hash: bool,
) -> tuple[bool, str]:
    repo_rel = local_file.relative_to(ROOT).as_posix()

    if changed is not None and not path_matches_delta(repo_rel, changed):
        return False, "delta-skip"

    try:
        remote_size = ftp.size(remote_file)
    except error_perm:
        return True, "missing-remote"

    local_size = local_file.stat().st_size
    if remote_size != local_size:
        return True, "size-diff"

    if not verify_hash:
        return False, "size-match"

    local_digest = file_hash(local_file)
    tmp_name = f".deploy-verify-{int(time.time() * 1000)}.tmp"
    try:
        with local_file.open("rb") as handle, open(tmp_name, "wb") as out:
            ftp.retrbinary(f"RETR {remote_file}", out.write)
        remote_digest = file_hash(Path(tmp_name))
    finally:
        if os.path.exists(tmp_name):
            os.remove(tmp_name)

    if local_digest != remote_digest:
        return True, "hash-diff"

    return False, "unchanged"


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
            return
        except Exception as exc:  # noqa: BLE001
            if attempt >= retries:
                raise
            print(f"retry {attempt}/{retries} {remote_file}: {exc}", file=sys.stderr)
            time.sleep(min(attempt * 2, 10))


def connect_ftp(host: str, user: str, password: str) -> FTP:
    ftp = FTP(host, timeout=120)
    ftp.login(user, password)
    ftp.set_pasv(True)
    return ftp


def collect_uploads(target: str, manifest: dict) -> list[tuple[Path, str]]:
    uploads: list[tuple[Path, str]] = []

    for mapping in manifest[target]["mappings"]:
        local = ROOT / mapping["local"]
        remote_base = mapping["remote"].strip("/")

        if mapping.get("file"):
            if local.is_file():
                uploads.append((local, remote_base))
            else:
                print(f"SKIP missing file {local}")
            continue

        if not local.is_dir():
            print(f"SKIP missing dir {local}")
            continue

        for file_path in iter_files(local):
            rel = file_path.relative_to(local).as_posix()
            remote = f"{remote_base}/{rel}" if remote_base else rel
            uploads.append((file_path, remote))

    return uploads


def parallel_workers() -> int:
    raw = os.environ.get("FTP_PARALLEL", "4").strip()
    try:
        value = int(raw)
    except ValueError:
        value = 4
    return max(1, min(value, 8))


def upload_batch(
    host: str,
    user: str,
    password: str,
    batch: list[tuple[Path, str]],
) -> list[str]:
    ftp = connect_ftp(host, user, password)
    uploaded: list[str] = []
    try:
        for local_file, remote_file in batch:
            upload_file(ftp, local_file, remote_file)
            uploaded.append(remote_file)
    finally:
        ftp.quit()
    return uploaded


def deploy_target(target: str, manifest: dict) -> None:
    cfg = manifest[target]
    user = os.environ[f"FTP_{target.upper()}_USER"]
    password = os.environ[f"FTP_{target.upper()}_PASSWORD"]
    host = cfg["ftp"]["host"]
    changed = load_changed_paths()
    verify_hash = os.environ.get("DEPLOY_VERIFY_HASH", "").strip() in {"1", "true", "yes"}
    workers = parallel_workers()

    mode = "full" if changed is None else f"delta ({len(changed)} git paths)"
    print(f"=== Deploy {target} -> {host} ({user}) | mode={mode} | parallel={workers} ===")

    uploads = collect_uploads(target, manifest)
    to_upload: list[tuple[Path, str]] = []
    skipped = 0
    skip_reasons: dict[str, int] = {}

    probe = connect_ftp(host, user, password)
    try:
        for local_file, remote_file in uploads:
            upload, reason = should_upload(local_file, remote_file, probe, changed, verify_hash=verify_hash)
            if upload:
                to_upload.append((local_file, remote_file))
            else:
                skipped += 1
                skip_reasons[reason] = skip_reasons.get(reason, 0) + 1
                print(f"SKIP {remote_file} ({reason})")
    finally:
        probe.quit()

    if not to_upload:
        print(f"NO_UPLOADS skipped={skipped} reasons={skip_reasons}")
    else:
        chunk_size = max(1, (len(to_upload) + workers - 1) // workers)
        batches = [to_upload[i : i + chunk_size] for i in range(0, len(to_upload), chunk_size)]

        with ThreadPoolExecutor(max_workers=min(workers, len(batches))) as pool:
            futures = [
                pool.submit(upload_batch, host, user, password, batch)
                for batch in batches
            ]
            for future in as_completed(futures):
                for remote_file in future.result():
                    print(f"OK {remote_file}")

        print(f"UPLOADED {len(to_upload)} skipped={skipped} reasons={skip_reasons}")

    setup_key = os.environ.get("SETUP_CACHE_KEY", "")
    github_sha = os.environ.get("GITHUB_SHA", "")
    for url_template in cfg.get("post_deploy", []):
        if not setup_key:
            print(f"SKIP post-deploy (SETUP_CACHE_KEY missing): {url_template}")
            continue
        url = url_template.replace("{SETUP_KEY}", setup_key).replace("{GITHUB_SHA}", github_sha)
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
    args = [arg for arg in sys.argv[1:] if not arg.startswith("-")]
    force_full = "--full" in sys.argv[1:]

    if force_full:
        os.environ["DEPLOY_FULL_SYNC"] = "1"

    targets = args or ["web", "admin"]

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
