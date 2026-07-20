<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

echo 'PHP_VERSION='.PHP_VERSION."\n";
echo 'SAPI='.PHP_SAPI."\n";
echo 'INI_LOADED='.(php_ini_loaded_file() ?: '')."\n";
echo 'INI_SCANNED='.(php_ini_scanned_files() ?: '')."\n";
echo 'EXTENSION_DIR='.ini_get('extension_dir')."\n";
echo 'disable_functions='.ini_get('disable_functions')."\n";
echo 'dl_enabled='.(function_exists('dl') && !in_array('dl', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true) ? 'yes' : 'NO')."\n";

$need = ['mbstring','openssl','pdo','pdo_mysql','tokenizer','xml','ctype','json','fileinfo','curl','zip','phar','intl','bcmath'];
foreach ($need as $ext) {
    echo 'ext.'.$ext.'='.(extension_loaded($ext) ? 'yes' : 'NO')."\n";
}
echo 'loaded='.implode(',', get_loaded_extensions())."\n";

$candidates = [
    '/opt/alt/php83/usr/lib64/php/modules',
    '/opt/alt/php83/usr/lib/php/modules',
    '/opt/cpanel/ea-php83/root/usr/lib64/php/modules',
    '/usr/lib64/php/modules',
    '/usr/local/lib/php/extensions',
];
foreach ($candidates as $d) {
    echo 'dir.'.$d.'='.(is_dir($d) ? 'yes' : 'no')."\n";
    if (!is_dir($d)) {
        continue;
    }
    $mods = @scandir($d) ?: [];
    $want = ['mbstring.so','pdo.so','pdo_mysql.so','fileinfo.so','zip.so','phar.so','intl.so','bcmath.so','curl.so','openssl.so'];
    foreach ($want as $m) {
        echo 'mod.'.$d.'/'.$m.'='.(in_array($m, $mods, true) ? 'yes' : 'NO')."\n";
    }
}

$home = getenv('HOME') ?: '';
echo 'HOME='.$home."\n";
echo 'DOCUMENT_ROOT='.($_SERVER['DOCUMENT_ROOT'] ?? '')."\n";
foreach ([$home.'/.cl.selector', dirname((string) ($_SERVER['DOCUMENT_ROOT'] ?? '')).'/.cl.selector', '/home/gonulkop/.cl.selector'] as $p) {
    echo 'clsel.'.$p.'='.(is_dir($p) ? 'dir' : (is_file($p) ? 'file' : 'no'))."\n";
    if (is_dir($p)) {
        $files = @scandir($p) ?: [];
        echo 'clsel_files.'.implode(',', array_slice(array_diff($files, ['.', '..']), 0, 40))."\n";
    }
}

echo "OK\n";
