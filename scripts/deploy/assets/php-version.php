<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo 'PHP_VERSION='.PHP_VERSION."\n";
echo 'SAPI='.PHP_SAPI."\n";
$need = ['mbstring','openssl','pdo','pdo_mysql','tokenizer','xml','ctype','json','fileinfo','curl','zip','phar','intl','bcmath'];
foreach ($need as $ext) {
    echo 'ext.'.$ext.'='.(extension_loaded($ext) ? 'yes' : 'NO')."\n";
}
echo 'class.ZipArchive='.(class_exists('ZipArchive') ? 'yes' : 'NO')."\n";
echo 'class.PharData='.(class_exists('PharData') ? 'yes' : 'NO')."\n";
echo "OK\n";
