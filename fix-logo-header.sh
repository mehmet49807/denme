#!/bin/bash
set -euo pipefail
upload_one() {
  local src="$1" dst="$2"
  local expected i
  expected=$(wc -c < "$src" | tr -d ' ')
  for i in $(seq 1 15); do
    echo "[$dst] attempt $i"
    if python3 - <<PY
from ftplib import FTP
src='$src'; dst='$dst'
ftp=FTP('ftp.gonulkoprusu.com',timeout=150)
ftp.login('web@gonulkoprusu.com','Mhmt498071'); ftp.set_pasv(True)
with open(src,'rb') as f: ftp.storbinary(f'STOR {dst}', f, 8192)
print(ftp.size(dst)); ftp.quit()
PY
    then
      SZ=$(python3 - <<PY
from ftplib import FTP
ftp=FTP('ftp.gonulkoprusu.com',timeout=30)
ftp.login('web@gonulkoprusu.com','Mhmt498071'); ftp.set_pasv(True)
print(ftp.size('$dst')); ftp.quit()
PY
)
      echo "[$dst] size=$SZ expected=$expected"
      if [ "$SZ" = "$expected" ]; then
        return 0
      fi
    fi
    sleep 6
  done
  return 1
}

python3 << 'PY'
import base64, json, os
files = {
  'resources/views/layouts/app.blade.php': '/workspace/web-site/resources/views/layouts/app.blade.php',
  'resources/views/partials/logo.blade.php': '/workspace/web-site/resources/views/partials/logo.blade.php',
  'resources/views/partials/logo-brand-css.blade.php': '/workspace/web-site/resources/views/partials/logo-brand-css.blade.php',
}
enc = {k: base64.b64encode(open(v,'rb').read()).decode() for k,v in files.items()}
php = "<?php\nif(($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') { http_response_code(403); exit('forbidden'); }\n$root = __DIR__;\n$files = json_decode(<<<'JSON'\n" + json.dumps(enc) + "\nJSON, true);\nforeach ($files as $rel => $b64) { $p = $root.'/'.$rel; @mkdir(dirname($p), 0755, true); file_put_contents($p, base64_decode($b64)); echo $rel.' '.filesize($p).\"\\n\"; }\n@shell_exec('cd '.escapeshellarg($root).' && php artisan view:clear 2>/dev/null');\necho \"OK\\n\";\n"
open('/workspace/patch-logo-header.php','w').write(php)
print('patch bytes', os.path.getsize('/workspace/patch-logo-header.php'))
PY

upload_one /workspace/patch-logo-header.php patch-logo-header.php
curl -sS "https://gonulkoprusu.com/patch-logo-header.php?key=gk-cpanel-setup-2026"
echo
curl -s "https://gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" | head -1
curl -sS "https://gonulkoprusu.com/" | rg 'site-logo--brand|site-logo-brand-img' | head -5
