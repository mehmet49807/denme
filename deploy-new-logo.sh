#!/bin/bash
set -euo pipefail
upload_file() {
  local src="$1" dst="$2" expected="$3"
  local i
  for i in $(seq 1 15); do
    echo "[$dst] attempt $i"
    if curl -sS --ftp-pasv --connect-timeout 45 --max-time 300 \
      -T "$src" -u 'web@gonulkoprusu.com:Mhmt498071' \
      "ftp://ftp.gonulkoprusu.com/$dst"; then
      SZ=$(python3 - <<PY
from ftplib import FTP
ftp=FTP('ftp.gonulkoprusu.com',timeout=30)
ftp.login('web@gonulkoprusu.com','Mhmt498071'); ftp.set_pasv(True)
print(ftp.size('$dst'))
ftp.quit()
PY
)
      echo "[$dst] size=$SZ expected=$expected"
      if [ "$SZ" = "$expected" ]; then
        return 0
      fi
    fi
    sleep 8
  done
  return 1
}

upload_file /workspace/web-site/resources/views/partials/logo.blade.php resources/views/partials/logo.blade.php $(wc -c < /workspace/web-site/resources/views/partials/logo.blade.php | tr -d ' ')
sleep 3
upload_file /workspace/web-site/resources/views/partials/logo-brand-css.blade.php resources/views/partials/logo-brand-css.blade.php $(wc -c < /workspace/web-site/resources/views/partials/logo-brand-css.blade.php | tr -d ' ')
sleep 3
for img in logo-180.png logo-220.png logo-320.png favicon.png apple-touch-icon.png gonul-koprusu-logo.png; do
  upload_file "/workspace/web-site/public/images/$img" "images/$img" $(wc -c < "/workspace/web-site/public/images/$img" | tr -d ' ')
  sleep 3
done
curl -s "https://gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" | head -2
echo DONE
