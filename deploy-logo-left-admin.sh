#!/bin/bash
set -euo pipefail
WEB='web@gonulkoprusu.com:Mhmt498071'
ADMIN='panel@admin.gonulkoprusu.com:Mehmt498071'

upload() {
  local userpass="$1" src="$2" dst="$3" expected="$4"
  local i sz
  for i in $(seq 1 12); do
    echo "[$dst] attempt $i"
    if curl -sS --ftp-pasv --connect-timeout 40 --max-time 180 \
      -T "$src" -u "$userpass" "ftp://ftp.gonulkoprusu.com/$dst"; then
      sz=$(python3 - <<PY
from ftplib import FTP
user, pw = '$userpass'.split(':', 1)
ftp=FTP('ftp.gonulkoprusu.com',timeout=25); ftp.login(user,pw); ftp.set_pasv(True)
print(ftp.size('$dst')); ftp.quit()
PY
)
      echo "[$dst] size=$sz expected=$expected"
      [ "$sz" = "$expected" ] && return 0
    fi
    sleep 5
  done
  return 1
}

python3 << 'PY'
import base64, json, os

def patch(files, out, extra_php=''):
    enc = {k: base64.b64encode(open(v,'rb').read()).decode() for k,v in files.items()}
    php = "<?php\nif(($_GET['key']??'')!=='gk-cpanel-setup-2026'){http_response_code(403);exit('forbidden');}\n$root=__DIR__;\n$files=json_decode(<<<'JSON'\n"+json.dumps(enc)+"\nJSON,true);\nforeach($files as $r=>$b){$p=$root.'/'.$r;@mkdir(dirname($p),0755,true);file_put_contents($p,base64_decode($b));echo $r.' '.filesize($p).\"\\n\";}\n"+extra_php+"@shell_exec('cd '.escapeshellarg($root).' && php artisan view:clear 2>/dev/null');\necho \"OK\\n\";\n"
    open(out,'w').write(php)
    print(out, os.path.getsize(out))

admin_brand_php = r"""
$viewsRoot=$root.'/resources/views';
$version='brand-v15';
$updated=[];
if(is_dir($viewsRoot)){
  $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsRoot,FilesystemIterator::SKIP_DOTS));
  foreach($it as $file){
    if(!$file->isFile()||$file->getExtension()!=='php'){continue;}
    $path=$file->getPathname();
    $content=file_get_contents($path);
    if($content===false||!str_contains($content,'logo')){continue;}
    $new=$content;
    $new=str_replace('logo-mark.png?v=brand-v1','logo-mark.png?v='.$version,$new);
    $new=str_replace('logo-admin.png?v=brand-v1','logo-admin.png?v='.$version,$new);
    $new=str_replace('favicon.png?v=brand-v1','favicon.png?v='.$version,$new);
  $new=preg_replace('/logo-mark\.png\?v=brand-v\d+/','logo-mark.png?v='.$version,$new)??$new;
  $new=preg_replace('/logo-admin\.png\?v=brand-v\d+/','logo-admin.png?v='.$version,$new)??$new;
    if($new!==$content){file_put_contents($path,$new);$updated[]=str_replace($root.'/','',$path);}
  }
}
foreach($updated as $f){echo 'view '.$f."\n";}
"""

web_files = {
    'resources/views/partials/logo-brand-css.blade.php': '/workspace/web-site/resources/views/partials/logo-brand-css.blade.php',
    'resources/views/partials/logo.blade.php': '/workspace/web-site/resources/views/partials/logo.blade.php',
    'images/logo-mark.png': '/workspace/web-site/public/images/logo-mark.png',
    'images/logo-admin.png': '/workspace/web-site/public/images/logo-admin.png',
}
admin_files = {
    'images/logo-mark.png': '/workspace/web-site/public/images/logo-mark.png',
    'images/logo-admin.png': '/workspace/web-site/public/images/logo-admin.png',
    'images/favicon.png': '/workspace/web-site/public/images/favicon.png',
    'images/logo-220.png': '/workspace/web-site/public/images/logo-220.png',
}
patch(web_files, '/workspace/patch-web-logo-left.php')
patch(admin_files, '/workspace/patch-admin-logo.php', admin_brand_php)
PY

upload "$WEB" /workspace/patch-web-logo-left.php patch-web-logo-left.php $(wc -c < /workspace/patch-web-logo-left.php | tr -d ' ')
curl -sS "https://gonulkoprusu.com/patch-web-logo-left.php?key=gk-cpanel-setup-2026"; echo
curl -s "https://gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" | head -1

upload "$ADMIN" /workspace/patch-admin-logo.php patch-admin-logo.php $(wc -c < /workspace/patch-admin-logo.php | tr -d ' ')
curl -sS "https://admin.gonulkoprusu.com/patch-admin-logo.php?key=gk-cpanel-setup-2026"; echo
upload "$ADMIN" /workspace/patch-admin-brand-version.php patch-admin-brand-version.php $(wc -c < /workspace/patch-admin-brand-version.php | tr -d ' ')
curl -sS "https://admin.gonulkoprusu.com/patch-admin-brand-version.php?key=gk-cpanel-setup-2026"; echo
curl -s "https://admin.gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026" 2>/dev/null | head -1 || true

curl -sS -o /dev/null -w "web:%{size_download} " "https://gonulkoprusu.com/"
curl -sS -o /dev/null -w "admin-logo:%{size_download}\n" "https://admin.gonulkoprusu.com/images/logo-mark.png?v=$(date +%s)"
echo DONE
