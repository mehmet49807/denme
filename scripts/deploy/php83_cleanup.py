#!/usr/bin/env python3
"""Remove temporary PHP 8.3 probe helpers from live web/admin docroots."""
from __future__ import annotations

import json
import os
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
manifest = json.loads((ROOT / "deploy/manifest.json").read_text())

REMOVE = [
    "php83-probe.php",
    "probe83.php",
    # keep php83-enable.php but replace with disabled stub
]


DISABLED_ENABLE = b"""<?php
header('Content-Type: text/plain; charset=utf-8');
http_response_code(410);
echo "DISABLED\\n";
echo "PHP 8.3 enable helper retired after Laravel 13 upgrade.\\n";
"""


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


def main() -> int:
    from io import BytesIO

    for target in ("web", "admin"):
        ftp = ftp_connect(target)
        try:
            names = []
            try:
                ftp.retrlines("NLST", names.append)
            except Exception as e:
                print(target, "NLST fail", e)
                names = []
            base = {n.split("/")[-1] for n in names}
            for name in REMOVE:
                if name in base or True:
                    try:
                        ftp.delete(name)
                        print("DELETED", target, name)
                    except Exception as e:
                        print("SKIP", target, name, e)
            ftp.storbinary("STOR php83-enable.php", BytesIO(DISABLED_ENABLE))
            print("DISABLED", target, "php83-enable.php")
        finally:
            ftp.quit()
    print("CLEANUP_OK")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
