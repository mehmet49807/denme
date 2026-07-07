#!/bin/bash
set -euo pipefail

upload() {
  local l="$1" r="$2"
  for i in 1 2 3 4 5 6 7 8 9 10; do
    curl -sS --connect-timeout 30 --max-time 300 -T "$l" -u 'web@gonulkoprusu.com:Mhmt498071' "ftp://ftp.gonulkoprusu.com/$r" && echo "OK $r" && return 0
    echo "retry $r $i"
    sleep 5
  done
  echo "FAIL $r" >&2
  return 1
}

# Canlı Laravel uygulaması public_html altında çalışıyor
upload /workspace/web-site/routes/web.php public_html/routes/web.php
upload /workspace/web-site/app/Http/Controllers/Web/LegalPageController.php public_html/app/Http/Controllers/Web/LegalPageController.php
upload /workspace/web-site/resources/views/web/blog.blade.php public_html/resources/views/web/blog.blade.php
upload /workspace/web-site/resources/views/web/blog-show.blade.php public_html/resources/views/web/blog-show.blade.php
upload /workspace/web-site/resources/views/web/sss.blade.php public_html/resources/views/web/sss.blade.php

# Üst dizin kopyası da güncellensin (varsa)
upload /workspace/web-site/routes/web.php routes/web.php || true
upload /workspace/web-site/app/Http/Controllers/Web/LegalPageController.php app/Http/Controllers/Web/LegalPageController.php || true

echo "Site fix deploy complete."
