#!/bin/bash
set -euo pipefail

WEB_USER='web@gonulkoprusu.com'
WEB_PASS='Mhmt498071'

upload() {
  local l="$1" r="$2"
  local attempt=1
  while [ "$attempt" -le 12 ]; do
    if curl -sS --connect-timeout 30 --max-time 300 \
      -T "$l" -u "$WEB_USER:$WEB_PASS" \
      "ftp://ftp.gonulkoprusu.com/$r"; then
      echo "OK $r"
      return 0
    fi
    echo "retry $r ($attempt)"
    attempt=$((attempt + 1))
    sleep 5
  done
  echo "FAIL $r" >&2
  return 1
}

echo "=== Deploying Blog/SSS fixes ==="

upload /workspace/web-site/routes/web.php routes/web.php
upload /workspace/web-site/resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php
upload /workspace/web-site/resources/views/layouts/content-page.blade.php resources/views/layouts/content-page.blade.php
upload /workspace/web-site/resources/views/partials/legal-nav.blade.php resources/views/partials/legal-nav.blade.php
upload /workspace/web-site/resources/views/web/blog.blade.php resources/views/web/blog.blade.php
upload /workspace/web-site/resources/views/web/blog-show.blade.php resources/views/web/blog-show.blade.php
upload /workspace/web-site/resources/views/web/sss.blade.php resources/views/web/sss.blade.php
upload /workspace/web-site/app/Http/Controllers/Web/LegalPageController.php app/Http/Controllers/Web/LegalPageController.php

echo "=== Clearing cache ==="
curl -sS "https://www.gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" || true
echo ""
curl -sS "https://www.gonulkoprusu.com/setup/diag-blog-sss?key=gk-cpanel-setup-2026" || true
echo ""
echo "=== Done ==="
