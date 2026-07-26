#!/usr/bin/env python3
"""Upload critical chicken files as soon as any FTP account accepts a login."""

from __future__ import annotations

import os
import sys
import time
from ftplib import FTP, error_perm
from pathlib import Path

HOST = os.environ.get("CHICKEN_FTP_HOST", "ftp.gonulkoprusu.com")
ROOT = Path("chicken")
CRITICAL = [
    "index.php",
    "tools/repair_app.php",
    "tools/server_bootstrap.php",
    "app/helpers.php",
    "app/MenuImageSync.php",
    "views/partials/menu_item_card.php",
    "views/layouts/public.php",
    "views/public/menu.php",
    "assets/css/app.css",
]


def candidates() -> list[tuple[str, str, str]]:
    out: list[tuple[str, str, str]] = []
    chicken_user = os.environ.get("CHICKEN_FTP_USER") or "chicken@gonulkoprusu.com"
    if os.environ.get("CHICKEN_FTP_PASSWORD"):
        out.append((chicken_user, os.environ["CHICKEN_FTP_PASSWORD"], "chicken"))
    if os.environ.get("FTP_WEB_USER") and os.environ.get("FTP_WEB_PASSWORD"):
        out.append((os.environ["FTP_WEB_USER"], os.environ["FTP_WEB_PASSWORD"], "web"))
    if os.environ.get("FTP_ADMIN_USER") and os.environ.get("FTP_ADMIN_PASSWORD"):
        out.append((os.environ["FTP_ADMIN_USER"], os.environ["FTP_ADMIN_PASSWORD"], "admin"))
    return out


def ensure_dir(ftp: FTP, remote_dir: str) -> None:
    parts = [p for p in remote_dir.split("/") if p]
    path = ""
    for part in parts:
        path += "/" + part
        try:
            ftp.mkd(path)
        except error_perm:
            pass


def find_bases(ftp: FTP, label: str) -> list[str]:
    bases: list[str] = []
    for path in (
        "/chicken.gonulkoprusu.com",
        "chicken.gonulkoprusu.com",
        "/chicken",
        "chicken",
        "/public_html/chicken",
        "public_html/chicken",
        "/",
    ):
        try:
            ftp.cwd(path)
            base = ftp.pwd().rstrip("/")
            names = [n.rstrip("/").split("/")[-1] for n in ftp.nlst()]
            if path in {"/", ""} and not ({"index.php", "assets", "app", "tools"} & set(names)):
                continue
            if base not in bases:
                bases.append(base)
                print("base", label, base, "names", names[:12])
        except Exception as exc:  # noqa: BLE001
            print("cwd skip", path, type(exc).__name__, exc)
    if label in {"web", "admin"}:
        for path in ("/chicken", "/public_html/chicken"):
            try:
                ensure_dir(ftp, path)
                ftp.cwd(path)
                base = ftp.pwd().rstrip("/")
                if base not in bases:
                    bases.append(base)
            except Exception as exc:  # noqa: BLE001
                print("create skip", path, exc)
    return bases


def upload(ftp: FTP, base: str, rel: str) -> None:
    local = ROOT / rel
    remote = f"{base}/{rel}" if base not in {"", "/"} else f"/{rel}"
    ensure_dir(ftp, str(Path(remote).parent).as_posix())
    with local.open("rb") as handle:
        ftp.storbinary(f"STOR {remote}", handle)
    size = ftp.size(remote)
    expected = local.stat().st_size
    if size is not None and int(size) != expected:
        raise RuntimeError(f"size mismatch {remote}: {size} != {expected}")
    if expected > 0 and size == 0:
        raise RuntimeError(f"remote empty {remote}")
    print("OK", remote, expected)


def main() -> int:
    creds = candidates()
    if not creds:
        print("No FTP credentials")
        return 1

    deadline = time.time() + 40 * 60
    attempt = 0
    while time.time() < deadline:
        attempt += 1
        for user, password, label in creds:
            print(f"attempt {attempt} {label}")
            ftp = None
            try:
                ftp = FTP()
                ftp.connect(HOST, 21, timeout=60)
                ftp.login(user, password)
                ftp.set_pasv(True)
                print("login ok", label, ftp.pwd())
                bases = find_bases(ftp, label)
                if not bases:
                    raise RuntimeError("no bases")
                for base in bases:
                    for rel in CRITICAL:
                        upload(ftp, base, rel)
                # menu images if time allows
                img_dir = ROOT / "assets/img/menu"
                for local in sorted(img_dir.glob("*.jpg")):
                    rel = local.relative_to(ROOT).as_posix()
                    for base in bases:
                        upload(ftp, base, rel)
                ftp.quit()
                print("EMERGENCY_RESTORE_DONE", label)
                return 0
            except Exception as exc:  # noqa: BLE001
                print("fail", label, type(exc).__name__, exc)
                if ftp is not None:
                    try:
                        ftp.quit()
                    except Exception:
                        pass
        time.sleep(15)
    print("EMERGENCY_RESTORE_TIMEOUT")
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
