#!/bin/bash
set -euo pipefail
WEB='web@gonulkoprusu.com:Mhmt498071'
upload() {
  for i in 1 2 3 4 5 6 7 8 9 10; do
    curl -sS --connect-timeout 30 --max-time 600 -T "$1" -u "$WEB" "ftp://ftp.gonulkoprusu.com/$2" && echo "OK $2" && return 0
    sleep 8
  done
  return 1
}
upload /workspace/home.blade.php resources/views/web/home.blade.php
upload /workspace/web-site/resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php
upload /workspace/web-site/public/.htaccess .htaccess
upload /workspace/web-site/routes/web.php routes/web.php
curl -s "https://www.gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" || true
echo DONE
