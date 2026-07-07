#!/bin/bash
set -euo pipefail

upload() {
  local l="$1" r="$2"
  for i in 1 2 3 4 5 6 7 8 9 10 11 12; do
    curl -sS --connect-timeout 30 --max-time 300 -T "$l" -u 'web@gonulkoprusu.com:Mhmt498071' "ftp://ftp.gonulkoprusu.com/$r" && echo "OK $r" && return 0
    echo "retry $r $i"
    sleep 5
  done
  return 1
}

upload /workspace/web-site/resources/views/layouts/app.blade.php public_html/resources/views/layouts/app.blade.php
upload /workspace/web-site/routes/web.php public_html/routes/web.php
upload /workspace/web-site/resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php || true
upload /workspace/web-site/routes/web.php routes/web.php || true

echo DONE
