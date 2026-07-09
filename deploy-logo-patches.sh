#!/bin/bash
set -euo pipefail
upload_patch() {
  local src="$1" name="$2" expected="$3"
  local i
  for i in $(seq 1 15); do
    echo "[$name] attempt $i"
    if curl -sS --ftp-pasv --connect-timeout 60 --max-time 300 \
      -T "$src" -u 'web@gonulkoprusu.com:Mhmt498071' \
      "ftp://ftp.gonulkoprusu.com/$name"; then
      SZ=$(python3 - <<PY
from ftplib import FTP
ftp=FTP('ftp.gonulkoprusu.com',timeout=30)
ftp.login('web@gonulkoprusu.com','Mhmt498071'); ftp.set_pasv(True)
print(ftp.size('$name'))
ftp.quit()
PY
)
      echo "size=$SZ expected=$expected"
      if [ "$SZ" = "$expected" ]; then
        return 0
      fi
    fi
    sleep 8
  done
  return 1
}

run_patch() {
  local php="$1"
  curl -sS "https://gonulkoprusu.com/$php?key=gk-cpanel-setup-2026"
  echo
}

upload_patch /workspace/patch-logo-220-png.php patch-logo-220.php 36395
run_patch patch-logo-220.php
sleep 2
upload_patch /workspace/patch-logo-180-png.php patch-logo-180.php 27147
run_patch patch-logo-180.php
sleep 2
upload_patch /workspace/patch-logo-320-png.php patch-logo-320.php 61331
run_patch patch-logo-320.php
sleep 2
upload_patch /workspace/patch-favicon-png.php patch-favicon.php 2133
run_patch patch-favicon.php
sleep 2
upload_patch /workspace/patch-apple-touch-icon-png.php patch-apple-touch-icon.php 24959
run_patch patch-apple-touch-icon.php
sleep 2
upload_patch /workspace/web-site/resources/views/partials/logo.blade.php resources/views/partials/logo.blade.php 733
sleep 2
curl -s "https://gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" | head -2
echo DONE
