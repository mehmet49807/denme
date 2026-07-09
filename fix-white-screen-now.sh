#!/bin/bash
set -euo pipefail
WEB='web@gonulkoprusu.com:Mhmt498071'
upload() {
  local src="$1" dst="$2" expected="$3"
  local i sz
  for i in $(seq 1 15); do
    echo "[$dst] attempt $i"
    if curl -sS --ftp-pasv --connect-timeout 45 --max-time 180 \
      -T "$src" -u "$WEB" "ftp://ftp.gonulkoprusu.com/$dst"; then
      sz=$(python3 - <<PY
from ftplib import FTP
ftp=FTP('ftp.gonulkoprusu.com',timeout=25)
ftp.login('web@gonulkoprusu.com','Mhmt498071'); ftp.set_pasv(True)
print(ftp.size('$dst'))
ftp.quit()
PY
)
      echo "[$dst] size=$sz expected=$expected"
      if [ "$sz" = "$expected" ]; then
        return 0
      fi
    fi
    sleep 5
  done
  return 1
}

# 1) HTTP patch (layout + logo + home)
upload /workspace/patch-white-screen.php patch-white-screen.php $(wc -c < /workspace/patch-white-screen.php | tr -d ' ')
curl -sS "https://gonulkoprusu.com/patch-white-screen.php?key=gk-cpanel-setup-2026"
echo

# 2) Direct FTP fallback for critical layout if still broken
SZ=$(curl -sS -o /dev/null -w "%{size_download}" "https://gonulkoprusu.com/" || echo 0)
if [ "$SZ" = "0" ]; then
  echo "Homepage still empty, uploading app.blade.php directly..."
  upload /workspace/web-site/resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php 7043
  upload /workspace/web-site/resources/views/partials/logo.blade.php resources/views/partials/logo.blade.php 733
fi

curl -s "https://gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" | head -1
curl -sS -o /dev/null -w "homepage:%{http_code} %{size_download}\n" "https://gonulkoprusu.com/"
curl -sS "https://gonulkoprusu.com/" | rg -n 'site-logo--brand|Gönülleri|DOCTYPE' | head -5
echo DONE
