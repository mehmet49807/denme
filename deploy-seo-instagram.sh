#!/bin/bash
set -euo pipefail

WEB='web@gonulkoprusu.com:Mhmt498071'

upload() {
  local src="$1" dst="$2"
  for i in 1 2 3 4 5 6 7 8 9 10; do
    curl -sS --connect-timeout 30 --max-time 300 -T "$src" -u "$WEB" "ftp://ftp.gonulkoprusu.com/$dst" && echo "OK $dst" && return 0
    sleep 8
  done
  return 1
}

upload /workspace/web-site/app/Support/SeoHelper.php app/Support/SeoHelper.php
upload /workspace/web-site/resources/views/partials/seo-head.blade.php resources/views/partials/seo-head.blade.php
upload /workspace/web-site/resources/views/partials/json-ld.blade.php resources/views/partials/json-ld.blade.php
upload /workspace/web-site/app/Http/Controllers/Web/LegalPageController.php app/Http/Controllers/Web/LegalPageController.php
upload /workspace/web-site/app/Http/Controllers/Web/HomeController.php app/Http/Controllers/Web/HomeController.php
upload /workspace/web-site/app/Http/Controllers/Web/SitemapController.php app/Http/Controllers/Web/SitemapController.php
upload /workspace/web-site/resources/views/web/blog.blade.php resources/views/web/blog.blade.php
upload /workspace/web-site/resources/views/web/blog-show.blade.php resources/views/web/blog-show.blade.php
upload /workspace/web-site/resources/views/web/sss.blade.php resources/views/web/sss.blade.php
upload /workspace/web-site/resources/views/web/about.blade.php resources/views/web/about.blade.php
upload /workspace/home.blade.php resources/views/web/home.blade.php

curl -s "https://www.gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" || true
echo DONE
