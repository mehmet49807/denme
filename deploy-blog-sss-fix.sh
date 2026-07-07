#!/bin/bash
set -euo pipefail

upload_file() {
  local user="$1" pass="$2" local_path="$3" remote_path="$4"
  local attempt=1
  while [ "$attempt" -le 8 ]; do
    if curl -sS --connect-timeout 20 --max-time 180 \
      -T "$local_path" \
      -u "$user:$pass" \
      "ftp://ftp.gonulkoprusu.com/$remote_path"; then
      echo "OK $remote_path"
      return 0
    fi
    echo "retry $remote_path ($attempt)"
    attempt=$((attempt + 1))
    sleep 4
  done
  echo "FAIL $remote_path" >&2
  return 1
}

ADMIN_USER='panel@admin.gonulkoprusu.com'
ADMIN_PASS='Mehmt498071'
WEB_USER='web@gonulkoprusu.com'
WEB_PASS='Mhmt498071'

echo "=== Admin FTP uploads ==="
upload_file "$ADMIN_USER" "$ADMIN_PASS" \
  /workspace/admin-panel/app/Http/Controllers/Admin/AdminAiController.php \
  app/Http/Controllers/Admin/AdminAiController.php

upload_file "$ADMIN_USER" "$ADMIN_PASS" \
  /workspace/admin-panel/app/Services/OpenRouterService.php \
  app/Services/OpenRouterService.php

upload_file "$ADMIN_USER" "$ADMIN_PASS" \
  /workspace/admin-panel/config/services.php \
  config/services.php

echo "=== Website FTP uploads ==="
upload_file "$WEB_USER" "$WEB_PASS" \
  /workspace/web-site/app/Services/PublishedBlogFaqService.php \
  app/Services/PublishedBlogFaqService.php

upload_file "$WEB_USER" "$WEB_PASS" \
  /workspace/web-site/app/Http/Controllers/Web/BlogPageController.php \
  app/Http/Controllers/Web/BlogPageController.php

upload_file "$WEB_USER" "$WEB_PASS" \
  /workspace/web-site/app/Http/Controllers/Web/SssPageController.php \
  app/Http/Controllers/Web/SssPageController.php

upload_file "$WEB_USER" "$WEB_PASS" \
  /workspace/web-site/app/Http/Controllers/Web/SeoPublishSyncController.php \
  app/Http/Controllers/Web/SeoPublishSyncController.php

upload_file "$WEB_USER" "$WEB_PASS" \
  /workspace/web-site/resources/views/web/blog-index.blade.php \
  resources/views/web/blog-index.blade.php

upload_file "$WEB_USER" "$WEB_PASS" \
  /workspace/web-site/resources/views/web/blog-show.blade.php \
  resources/views/web/blog-show.blade.php

upload_file "$WEB_USER" "$WEB_PASS" \
  /workspace/web-site/resources/views/web/sss.blade.php \
  resources/views/web/sss.blade.php

upload_file "$WEB_USER" "$WEB_PASS" \
  /workspace/web-site/routes/web.php \
  routes/web.php

echo "Deploy complete."
