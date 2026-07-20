<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
$key = (string) ($_GET['key'] ?? '');
if ($key !== 'gk-laravel-update-2026' && $key !== 'gk-cpanel-setup-2026') { http_response_code(403); echo "FORBIDDEN\n"; exit; }
$action = (string) ($_GET['action'] ?? 'info');
$need = ['mbstring','pdo','pdo_mysql','fileinfo','zip','phar','intl','bcmath'];
function line($k,$v){ echo $k.'='.$v."\n"; }
function run($cmd){ $lines=[]; $code=-1; @exec($cmd.' 2>&1',$lines,$code); return [$code, implode("\n",$lines)]; }
function dump_exts($need){ foreach($need as $e) line('ext.'.$e, extension_loaded($e)?'yes':'NO'); }

line('PHP_VERSION', PHP_VERSION);
line('INI_SCANNED', (string)(php_ini_scanned_files()?:''));
dump_exts($need);

$ini82 = '/etc/cl.php.d/alt-php82/alt_php.ini';
$ini83 = '/etc/cl.php.d/alt-php83/alt_php.ini';
$conf83 = '/opt/alt/php83/link/conf';
$conf83bak = '/opt/alt/php83/link/conf.bak-native';

if ($action === 'info') {
  foreach ([$ini82,$ini83,$conf83,$conf83bak,'/opt/alt/php82/link/conf','/opt/alt/php83/etc/php.d'] as $p) {
    $t = is_link($p) ? ('link->'.@readlink($p)) : (is_dir($p)?'dir':(is_file($p)?'file':'missing'));
    line('path.'.$p, $t.';w='.(file_exists($p)&&is_writable($p)?'1':'0'));
  }
  foreach ([$ini82,$ini83] as $p) {
    if (is_readable($p)) {
      echo "---- BEGIN $p ----\n".file_get_contents($p)."\n---- END ----\n";
    }
  }
  // native php.d
  $native = '/opt/alt/php83/etc/php.d';
  if (is_dir($native)) {
    line('list.native', implode(',', array_diff(@scandir($native)?:[], ['.','..'])));
    foreach (['default.ini','mysqli.ini'] as $f) {
      $fp = $native.'/'.$f;
      if (is_readable($fp)) echo "---- BEGIN $fp ----\n".file_get_contents($fp)."\n---- END ----\n";
    }
  }
  if (is_dir($conf83bak)) {
    line('list.bak', implode(',', array_diff(@scandir($conf83bak)?:[], ['.','..'])));
  }
  echo "OK\n"; exit;
}

if ($action === 'fix') {
  // Restore conf symlink/dir if broken
  if (!file_exists($conf83) && file_exists($conf83bak)) {
    [$c,$o] = run('mv '.escapeshellarg($conf83bak).' '.escapeshellarg($conf83));
    line('restore_conf', "code=$c out=$o");
  }
  // Ensure conf points to /etc/cl.php.d/alt-php83 like php82
  $desired = '/etc/cl.php.d/alt-php83';
  $current = is_link($conf83) ? @readlink($conf83) : '';
  line('conf_before', is_link($conf83) ? ('link:'.$current) : (file_exists($conf83)?'exists':'missing'));
  if ($current !== $desired) {
    if (file_exists($conf83) || is_link($conf83)) {
      [$c,$o] = run('rm -rf '.escapeshellarg($conf83));
      line('rm_conf', "code=$c");
    }
    [$c,$o] = run('ln -s '.escapeshellarg($desired).' '.escapeshellarg($conf83));
    line('ln_conf', "code=$c out=$o");
  }

  // Build alt_php.ini for 8.3 based on 8.2 if possible
  $src = is_readable($ini82) ? (string)file_get_contents($ini82) : '';
  echo "---- SRC82 ----\n$src\n---- END ----\n";

  $modules = ['bcmath','dom','fileinfo','gd','igbinary','imagick','imap','intl','mbstring','mcrypt','memcache','memcached','mysqlnd','nd_mysqli','nd_pdo_mysql','opcache','pdo','pdo_sqlite','phar','posix','soap','sockets','tidy','xmlreader','xmlrpc','xmlwriter','xsl','zip'];
  // Also include classic pdo_mysql as belt-and-suspenders
  $modules = array_values(array_unique(array_merge($modules, ['pdo_mysql','mysqli'])));

  $body = "; CloudLinux PHP Selector style — Laravel 13 enable\n";
  $body .= "; generated ".gmdate('c')."\n";
  if ($src !== '') {
    // Prefer copying 8.2 ini and adjusting if needed
    $body = $src;
    // ensure extension lines for required modules exist
    foreach ($modules as $m) {
      if (!preg_match('/^\s*extension\s*=\s*'.preg_quote($m,'/').'(\.so)?\s*$/mi', $body)
        && !preg_match('/^\s*zend_extension\s*=\s*'.preg_quote($m,'/').'/mi', $body)) {
        // only add if .so exists
        $so = '/opt/alt/php83/usr/lib64/php/modules/'.$m.'.so';
        if (is_file($so)) {
          $body .= "\nextension={$m}.so\n";
        }
      }
    }
  } else {
    foreach ($modules as $m) {
      $so = '/opt/alt/php83/usr/lib64/php/modules/'.$m.'.so';
      if (is_file($so)) $body .= "extension={$m}.so\n";
    }
  }

  $ok = @file_put_contents($ini83, $body) !== false;
  line('write_ini83', $ok ? 'ok' : 'FAIL');
  line('ini83_len', (string)strlen($body));
  echo "---- NEW83 ----\n$body\n---- END ----\n";

  // Also write individual ini files in alt-php83 dir (some setups scan all)
  $dir = '/etc/cl.php.d/alt-php83';
  if (is_dir($dir) && is_writable($dir)) {
    foreach (['mbstring','pdo','pdo_mysql','fileinfo','zip','phar','intl','bcmath','mysqlnd','nd_mysqli','nd_pdo_mysql'] as $m) {
      $so = '/opt/alt/php83/usr/lib64/php/modules/'.$m.'.so';
      if (!is_file($so)) continue;
      @file_put_contents($dir.'/'.$m.'.ini', "extension={$m}.so\n");
    }
    line('list83', implode(',', array_diff(@scandir($dir)?:[], ['.','..'])));
  }

  clearstatcache(true);
  line('conf_after', is_link($conf83) ? ('link:'.@readlink($conf83)) : (file_exists($conf83)?'exists':'missing'));
  line('INI_SCANNED_NOW', (string)(php_ini_scanned_files()?:''));
  dump_exts($need);
  echo "OK\n"; exit;
}

if ($action === 'restore') {
  // put conf back to native php.d if needed
  if (is_link($conf83)) {
    run('rm -f '.escapeshellarg($conf83));
  }
  if (!file_exists($conf83) && file_exists($conf83bak)) {
    run('mv '.escapeshellarg($conf83bak).' '.escapeshellarg($conf83));
  } elseif (!file_exists($conf83)) {
    run('ln -s /opt/alt/php83/etc/php.d '.escapeshellarg($conf83));
  }
  // restore minimal alt-php83
  @file_put_contents($ini83, "; placeholder\n");
  foreach (glob('/etc/cl.php.d/alt-php83/*.ini') ?: [] as $f) {
    if (basename($f) !== 'alt_php.ini') @unlink($f);
  }
  $cfg='/home/gonulkop/.cl.selector/defaults.cfg';
  $bak=$cfg.'.bak-laravel13';
  if (is_readable($bak)) @file_put_contents($cfg, file_get_contents($bak));
  echo "OK\n"; exit;
}

echo "FAIL unknown\n";
