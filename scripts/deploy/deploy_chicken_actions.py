#!/usr/bin/env python3
"""Deploy chicken/ via GitHub Actions FTP secrets (chicken, web, or admin)."""

from __future__ import annotations

import os
import sys
import time
from ftplib import FTP, error_perm
from io import BytesIO
from pathlib import Path

ROOT = Path("chicken")
HOST = os.environ.get("CHICKEN_FTP_HOST", "ftp.gonulkoprusu.com")
SKIP = {".git", ".DS_Store", "Thumbs.db", "config.local.php"}

# Upload these first so a mid-deploy timeout cannot wipe the app entrypoint.
PRIORITY = [
    "index.php",
    ".htaccess",
    "app/helpers.php",
    "app/MenuImageSync.php",
    "app/MenuItemSync.php",
    "app/SchemaSync.php",
    "app/CategorySync.php",
    "app/Database.php",
    "app/Auth.php",
    "app/OrderService.php",
    "app/Router.php",
    "views/partials/menu_item_card.php",
    "views/layouts/public.php",
    "views/layouts/staff.php",
    "views/public/menu.php",
    "views/public/home.php",
    "views/public/order.php",
    "assets/css/app.css",
    "assets/js/app.js",
    "assets/img/brand-crisp-co-v3.png",
    "assets/img/logo-crisp-co.png",
    "assets/img/logo-mark.png",
    "assets/img/logo.png",
    "tools/server_bootstrap.php",
    "tools/repair_app.php",
]


def ensure_dir(ftp: FTP, remote_dir: str) -> None:
    parts = [p for p in remote_dir.split("/") if p]
    path = ""
    for part in parts:
        path += "/" + part
        try:
            ftp.mkd(path)
        except error_perm:
            pass


def connect(user: str, password: str, attempts: int = 12) -> FTP:
    last: Exception | None = None
    for i in range(attempts):
        try:
            print(f"connect try {i + 1}/{attempts} {HOST}", flush=True)
            ftp = FTP()
            ftp.connect(HOST, 21, timeout=90)
            ftp.login(user, password)
            ftp.set_pasv(True)
            try:
                ftp.encoding = "utf-8"
            except Exception:
                pass
            try:
                ftp.sock.settimeout(180)
            except Exception:
                pass
            print(f"login ok attempt={i + 1} pwd={ftp.pwd()}", flush=True)
            return ftp
        except Exception as exc:  # noqa: BLE001
            last = exc
            print(f"connect fail {i + 1}: {type(exc).__name__}: {exc}", flush=True)
            time.sleep(min(20, 4 * (i + 1)))
    raise RuntimeError(f"FTP connect failed: {last}")


def upload_one(ftp: FTP, user: str, password: str, local: Path, remote: str) -> FTP:
    for attempt in range(5):
        try:
            ensure_dir(ftp, str(Path(remote).parent).replace("\\", "/"))
            with local.open("rb") as handle:
                ftp.storbinary(f"STOR {remote}", handle)
            try:
                remote_size = ftp.size(remote)
            except Exception:
                remote_size = None
            local_size = local.stat().st_size
            if remote_size is not None and int(remote_size) != local_size:
                raise RuntimeError(f"size mismatch {remote_size}!={local_size}")
            if local_size > 0 and remote_size == 0:
                raise RuntimeError(f"remote empty after upload: {remote}")
            print("OK", remote, local_size)
            return ftp
        except Exception as exc:  # noqa: BLE001
            print(f"retry {remote} #{attempt + 1}: {type(exc).__name__}: {exc}")
            try:
                ftp.quit()
            except Exception:
                pass
            time.sleep(2 * (attempt + 1))
            ftp = connect(user, password)
    raise RuntimeError(f"failed to upload {remote}")


def list_files() -> list[Path]:
    repair_only = os.environ.get("CHICKEN_REPAIR_ONLY", "").strip() in {"1", "true", "yes"}
    files = [
        p
        for p in ROOT.rglob("*")
        if p.is_file() and p.name not in SKIP and "config.local.php" not in p.parts
    ]
    priority_paths = []
    for rel in PRIORITY:
        path = ROOT / rel
        if path.is_file():
            priority_paths.append(path)
    if repair_only:
        # Also include menu photos so the restored menu is not blank.
        images = [
            p
            for p in files
            if "assets/img/menu" in p.as_posix() and p.suffix.lower() in {".jpg", ".jpeg", ".png", ".webp"}
        ]
        print("REPAIR_ONLY=1 priority", len(priority_paths), "images", len(images))
        return priority_paths + images
    rest = [p for p in files if p not in priority_paths]
    # Images after PHP/CSS so the app boots even if image upload times out.
    images = [p for p in rest if "assets/img/menu" in p.as_posix()]
    other = [p for p in rest if p not in images]
    return priority_paths + other + images


def write_config(ftp: FTP, base: str) -> None:
    db_pass = os.environ.get("CHICKEN_DB_PASS", "")
    if not db_pass:
        return
    cfg = (
        "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
        "    'app_url' => 'https://gonulkoprusu.com/chicken',\n"
        "    'db' => [\n"
        "        'host' => 'localhost',\n"
        "        'port' => 3306,\n"
        "        'name' => "
        + repr(os.environ.get("CHICKEN_DB_NAME", "gonulkop_chicken"))
        + ",\n"
        "        'user' => "
        + repr(os.environ.get("CHICKEN_DB_USER", "gonulkop_admin"))
        + ",\n"
        "        'pass' => "
        + repr(db_pass)
        + ",\n"
        "        'charset' => 'utf8mb4',\n"
        "    ],\n"
        "];\n"
    )
    remote_cfg = f"{base}/config/config.local.php" if base else "/config/config.local.php"
    ensure_dir(ftp, str(Path(remote_cfg).parent).replace("\\", "/"))
    ftp.storbinary(f"STOR {remote_cfg}", BytesIO(cfg.encode("utf-8")))
    print("OK config/config.local.php")


def upload_files(ftp: FTP, user: str, password: str, base: str, files: list[Path]) -> FTP:
    print(f"Uploading {len(files)} files to {base or '/'}")
    for idx, local in enumerate(files, 1):
        rel = local.relative_to(ROOT).as_posix()
        remote = f"{base}/{rel}" if base else f"/{rel}"
        ftp = upload_one(ftp, user, password, local, remote)
        if idx % 8 == 0:
            try:
                ftp.voidcmd("NOOP")
            except Exception:
                try:
                    ftp.quit()
                except Exception:
                    pass
                ftp = connect(user, password)
    return ftp


def upload_tree(ftp: FTP, user: str, password: str, base: str) -> FTP:
    files = list_files()
    priority = [p for p in files if p.relative_to(ROOT).as_posix() in set(PRIORITY)]
    rest = [p for p in files if p not in priority]

    # Phase 1: restore bootable app immediately.
    ftp = upload_files(ftp, user, password, base, priority)
    write_config(ftp, base)
    print("PHASE1_DONE", base)

    # Phase 2: remaining views/assets/images.
    if rest:
        ftp = upload_files(ftp, user, password, base, rest)
    print("PHASE2_DONE", base)
    return ftp


def find_chicken_base(ftp: FTP, label: str) -> str | None:
    candidates = [
        "/chicken.gonulkoprusu.com",
        "chicken.gonulkoprusu.com",
        "/chicken.gonulkoprusu.com/public_html",
        "/domains/chicken.gonulkoprusu.com/public_html",
        "/home/gonulkop/chicken.gonulkoprusu.com",
        "/chicken",
        "chicken",
        "/public_html/chicken",
        "public_html/chicken",
    ]
    for path in candidates:
        try:
            ftp.cwd(path)
            base = ftp.pwd().rstrip("/")
            print("found existing base", base)
            return base
        except error_perm as exc:
            print("cwd fail", path, exc)

    try:
        ftp.cwd("/")
        names = ftp.nlst()
        print("search names", names[:120])
        for name in names:
            leaf = name.rstrip("/").split("/")[-1]
            if "chicken" in leaf.lower():
                try:
                    ftp.cwd(name)
                    base = ftp.pwd().rstrip("/")
                    print("found searched base", base)
                    return base
                except error_perm:
                    continue
    except Exception as exc:  # noqa: BLE001
        print("search fail", exc)

    if label in {"web", "admin"}:
        for path in ("/chicken", "/public_html/chicken"):
            try:
                ensure_dir(ftp, path)
                ftp.cwd(path)
                base = ftp.pwd().rstrip("/")
                print("created base", base)
                return base
            except Exception as exc:  # noqa: BLE001
                print("create fail", path, exc)
    return None


def bootstrap_subdomain() -> None:
    import secrets as _secrets
    import urllib.request

    token = _secrets.token_urlsafe(16)
    bootstrap_urls = [
        f"https://gonulkoprusu.com/public_html/chicken/tools/server_bootstrap.php?key={token}&expect={token}",
        f"https://gonulkoprusu.com/chicken/tools/server_bootstrap.php?key={token}&expect={token}",
    ]
    for url in bootstrap_urls:
        try:
            print("bootstrap try", url.split("?")[0])
            req = urllib.request.Request(
                url,
                headers={
                    "User-Agent": (
                        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                        "AppleWebKit/537.36 (KHTML, like Gecko) "
                        "Chrome/126.0.0.0 Safari/537.36"
                    ),
                    "Accept": "text/plain,*/*",
                },
            )
            with urllib.request.urlopen(req, timeout=90) as resp:
                body = resp.read().decode("utf-8", "replace")
            print(body[:2000])
            if "COPIED=" in body or "\nOK\n" in body or body.strip().endswith("OK"):
                break
        except Exception as exc:  # noqa: BLE001
            print("bootstrap failed", type(exc).__name__, exc)


def main() -> int:
    if not ROOT.is_dir():
        print("Missing chicken/", file=sys.stderr)
        return 1

    candidates: list[tuple[str, str, str]] = []
    chicken_user = os.environ.get("CHICKEN_FTP_USER") or "chicken@gonulkoprusu.com"
    if os.environ.get("CHICKEN_FTP_PASSWORD"):
        candidates.append((chicken_user, os.environ["CHICKEN_FTP_PASSWORD"], "chicken"))
    if os.environ.get("FTP_WEB_USER") and os.environ.get("FTP_WEB_PASSWORD"):
        candidates.append((os.environ["FTP_WEB_USER"], os.environ["FTP_WEB_PASSWORD"], "web"))
    if os.environ.get("FTP_ADMIN_USER") and os.environ.get("FTP_ADMIN_PASSWORD"):
        candidates.append((os.environ["FTP_ADMIN_USER"], os.environ["FTP_ADMIN_PASSWORD"], "admin"))

    if not candidates:
        print("::error::No FTP credentials available")
        return 1

    last_err: Exception | None = None
    uploaded_bases: list[str] = []

    for user, password, label in candidates:
        print(f"Trying FTP as {label} ({user})")
        ftp = None
        try:
            ftp = connect(user, password)
            bases: list[str] = []
            primary = find_chicken_base(ftp, label)
            if primary:
                bases.append(primary)
            if label in {"web", "admin"}:
                for extra in ("/chicken", "/public_html/chicken"):
                    try:
                        ensure_dir(ftp, extra)
                        ftp.cwd(extra)
                        b = ftp.pwd().rstrip("/")
                        if b not in bases:
                            bases.append(b)
                    except Exception as exc:  # noqa: BLE001
                        print("extra base fail", extra, exc)
            if not bases:
                raise RuntimeError("Could not locate/create chicken document root")
            for base in bases:
                ftp = upload_tree(ftp, user, password, base)
                uploaded_bases.append(f"{label}:{base}")
            try:
                ftp.quit()
            except Exception:
                pass
            if label == "chicken":
                print("Deploy finished via chicken account")
                print("UPLOADED", ",".join(uploaded_bases))
                bootstrap_subdomain()
                return 0
        except Exception as exc:  # noqa: BLE001
            last_err = exc
            print(f"Failed via {label}: {type(exc).__name__}: {exc}")
            if ftp is not None:
                try:
                    ftp.quit()
                except Exception:
                    pass

    if uploaded_bases:
        print("Deploy finished via fallback account(s)")
        print("UPLOADED", ",".join(uploaded_bases))
        bootstrap_subdomain()
        print(
            "::warning::Uploaded under main site FTP. "
            "If chicken.gonulkoprusu.com is still 404, point its document root to public_html/chicken "
            "or fix chicken FTP password for direct deploy."
        )
        return 0

    print("::error::All FTP strategies failed:", last_err)
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
