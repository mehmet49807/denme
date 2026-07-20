<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'gk-laravel-update-2026' && $key !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    echo "FORBIDDEN\n";
    exit;
}

$action = (string) ($_GET['action'] ?? 'info');

function line(string $k, string $v): void
{
    echo $k.'='.$v."\n";
}

line('PHP_VERSION', PHP_VERSION);
line('SAPI', PHP_SAPI);
line('INI_LOADED', (string) (php_ini_loaded_file() ?: ''));
line('INI_SCANNED', (string) (php_ini_scanned_files() ?: ''));
line('EXTENSION_DIR', (string) ini_get('extension_dir'));
line('UID', (string) getmyuid());
line('USER', (string) (function_exists('posix_getpwuid') ? (@posix_getpwuid(getmyuid())['name'] ?? '') : ''));
line('DOCUMENT_ROOT', (string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));

$paths = [
    '/home/gonulkop/.cl.selector',
    '/home/gonulkop/.cl.selector/defaults.cfg',
    '/opt/alt/php83/link/conf',
    '/opt/alt/php83/etc/php.ini',
    '/opt/alt/php83/etc/php.d',
];

foreach ($paths as $p) {
    $exists = file_exists($p) || is_dir($p);
    $readable = $exists && is_readable($p);
    $writable = $exists && is_writable($p);
    line('path.'.$p, ($exists ? 'exists' : 'missing').';r='.($readable ? '1' : '0').';w='.($writable ? '1' : '0'));
    if (is_dir($p) && $readable) {
        $files = @scandir($p) ?: [];
        line('list.'.$p, implode(',', array_slice(array_values(array_diff($files, ['.', '..'])), 0, 80)));
    }
    if (is_file($p) && $readable && filesize($p) < 20000) {
        echo "---- BEGIN $p ----\n";
        echo file_get_contents($p);
        echo "\n---- END $p ----\n";
    }
}

$need = ['mbstring', 'pdo', 'pdo_mysql', 'fileinfo', 'zip', 'phar', 'intl', 'bcmath'];
foreach ($need as $ext) {
    line('ext.'.$ext, extension_loaded($ext) ? 'yes' : 'NO');
}

if ($action === 'enable') {
    $confDir = '/opt/alt/php83/link/conf';
    $selectorDir = '/home/gonulkop/.cl.selector';
    $modules = [
        'mbstring', 'pdo', 'pdo_mysql', 'fileinfo', 'zip', 'phar', 'intl', 'bcmath',
        'tokenizer', 'xml', 'ctype', 'json', 'curl', 'openssl',
    ];

    if (is_dir($confDir) && is_writable($confDir)) {
        foreach ($modules as $m) {
            $ini = $confDir.'/'.$m.'.ini';
            $body = "; enabled by gonulkoprusu php83-enable\nextension={$m}.so\n";
            $ok = @file_put_contents($ini, $body) !== false;
            line('write.conf.'.$m, $ok ? 'ok' : 'FAIL');
        }
    } else {
        line('write.conf', 'not_writable');
    }

    // CloudLinux selector style configs (best-effort)
    if (is_dir($selectorDir) && is_writable($selectorDir)) {
        $cfg = $selectorDir.'/defaults.cfg';
        $current = is_readable($cfg) ? (string) file_get_contents($cfg) : '';
        line('defaults.cfg.before_len', (string) strlen($current));
        echo "---- BEGIN defaults.cfg BEFORE ----\n".$current."\n---- END ----\n";

        // Common CloudLinux PHP Selector formats tried historically
        $candidates = [
            "php_version=8.3\n",
            "[php]\nversion=8.3\n",
        ];
        // Also write modules list file variants
        $modList = implode("\n", $modules)."\n";
        foreach (['alt-php83.modules', 'php83.modules', '8.3.modules'] as $name) {
            $ok = @file_put_contents($selectorDir.'/'.$name, $modList) !== false;
            line('write.selector.'.$name, $ok ? 'ok' : 'FAIL');
        }

        // Try writing interpreter map if htaccess_cache exists
        $htc = $selectorDir.'/htaccess_cache';
        if (is_readable($htc)) {
            echo "---- BEGIN htaccess_cache ----\n".file_get_contents($htc)."\n---- END ----\n";
        }
    } else {
        line('write.selector', 'not_writable');
    }

    // Retry loading via phar/zip not possible; report after write
    clearstatcache();
    foreach ($need as $ext) {
        line('ext_after.'.$ext, extension_loaded($ext) ? 'yes' : 'NO');
    }
    line('NOTE', 'Extensions load on next request after conf write; call action=info again');
}

echo "OK\n";
