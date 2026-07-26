#!/usr/bin/env python3
"""Deploy chicken/ via GitHub Actions FTP secrets (chicken, web, or admin)."""

from __future__ import annotations

import os
import sys
from ftplib import FTP, error_perm
from io import BytesIO
from pathlib import Path

ROOT = Path("chicken")
HOST = os.environ.get("CHICKEN_FTP_HOST", "ftp.gonulkoprusu.com")
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


def upload_tree(ftp: FTP, base: str) -> None:
    files = [
        p
        for p in ROOT.rglob("*")
        if p.is_file() and p.name not in SKIP and "config.local.php" not in p.parts
    ]
    print(f"Uploading {len(files)} files to {base or '/'}")
    for local in files:
        rel = local.relative_to(ROOT).as_posix()
        remote = f"{base}/{rel}" if base else f"/{rel}"
        ensure_dir(ftp, str(Path(remote).parent).replace("\\", "/"))
        with local.open("rb") as handle:
            ftp.storbinary(f"STOR {remote}", handle)
        print("OK", rel)

    db_pass = os.environ.get("CHICKEN_DB_PASS", "")
    if db_pass:
        cfg = (
            "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            "    'app_url' => 'https://chicken.gonulkoprusu.com',\n"
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

    # Search one level up / around home for chicken-named dirs
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
                    # prefer public_html child if present
                    try:
                        children = ftp.nlst()
                        if any(c.split("/")[-1] == "public_html" for c in children):
                            ftp.cwd("public_html")
                            base = ftp.pwd().rstrip("/")
                    except Exception:
                        pass
                    print("discovered base", base)
                    return base
                except error_perm:
                    continue
    except Exception as exc:
        print("search failed", exc)

    if label == "chicken":
        home = ftp.pwd().rstrip("/")
        print("chicken account home as base", home or "/")
        return home

    if label in {"web", "admin"}:
        # Prefer web-root /chicken (FTP jail root is the live web root on this host).
        # Also create public_html/chicken for cPanel document-root remapping.
        created: list[str] = []
        for path in ["/chicken", "chicken", "/public_html/chicken", "public_html/chicken"]:
            try:
                ensure_dir(ftp, path if path.startswith("/") else "/" + path)
                ftp.cwd(path)
                base = ftp.pwd().rstrip("/")
                print("created fallback base", base)
                created.append(base)
            except Exception as exc:
                print("fallback fail", path, exc)
        if created:
            # Prefer shortest /chicken web-root path
            for base in created:
                if base.rstrip("/").endswith("/chicken") and "public_html" not in base:
                    return base
            return created[0]

    return None


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
        try:
            ftp = FTP()
            ftp.connect(HOST, 21, timeout=40)
            ftp.login(user, password)
            ftp.set_pasv(True)
            print("login ok, pwd=", ftp.pwd())
            bases = []
            primary = find_chicken_base(ftp, label)
            if primary:
                bases.append(primary)
            # Always also ensure web-root /chicken when using main accounts
            if label in {"web", "admin"}:
                for extra in ("/chicken", "/public_html/chicken"):
                    try:
                        ensure_dir(ftp, extra)
                        ftp.cwd(extra)
                        b = ftp.pwd().rstrip("/")
                        if b not in bases:
                            bases.append(b)
                    except Exception as exc:
                        print("extra base fail", extra, exc)
            if not bases:
                raise RuntimeError("Could not locate/create chicken document root")
            for base in bases:
                upload_tree(ftp, base)
                uploaded_bases.append(f"{label}:{base}")
            ftp.quit()
            if label == "chicken":
                print("Deploy finished via chicken account")
                print("UPLOADED", ",".join(uploaded_bases))
                return 0
        except Exception as exc:  # noqa: BLE001
            last_err = exc
            print(f"Failed via {label}: {type(exc).__name__}: {exc}")

    if uploaded_bases:
        print("Deploy finished via fallback account(s)")
        print("UPLOADED", ",".join(uploaded_bases))
        # Try server-side copy into subdomain docroot if PHP can see it.
        import urllib.request
        import secrets as _secrets

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
                with urllib.request.urlopen(req, timeout=60) as resp:
                    body = resp.read().decode("utf-8", "replace")
                print(body[:2000])
                if "COPIED=" in body or "\nOK\n" in body or body.strip().endswith("OK"):
                    break
            except Exception as exc:  # noqa: BLE001
                print("bootstrap failed", type(exc).__name__, exc)

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
