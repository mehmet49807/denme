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
$need = ['mbstring', 'pdo', 'pdo_mysql', 'fileinfo', 'zip', 'phar', 'intl', 'bcmath'];
$ini82 = '/etc/cl.php.d/alt-php82/alt_php.ini';
$conf83 = '/opt/alt/php83/link/conf';
$modulesDir = '/opt/alt/php83/usr/lib64/php/modules';

function line($k, $v)
{
    echo $k.'='.$v."\n";
}
function run($cmd)
{
    $lines = [];
    $code = -1;
    @exec($cmd.' 2>&1', $lines, $code);

    return [$code, implode("\n", $lines)];
}
function dump_exts($need)
{
    foreach ($need as $e) {
        line('ext.'.$e, extension_loaded($e) ? 'yes' : 'NO');
    }
}

line('PHP_VERSION', PHP_VERSION);
line('INI_SCANNED', (string) (php_ini_scanned_files() ?: ''));
dump_exts($need);

if ($action === 'info') {
    foreach ([$conf83, $ini82, '/opt/alt/php83/link', $modulesDir] as $p) {
        $t = is_link($p) ? ('link->'.@readlink($p)) : (is_dir($p) ? 'dir' : (is_file($p) ? 'file' : 'missing'));
        line('path.'.$p, $t.';w='.(file_exists($p) && is_writable(is_link($p) ? dirname($p) : $p) ? '1' : '0'));
    }
    if (is_readable($conf83) || is_dir($conf83)) {
        $files = is_dir($conf83) ? (@scandir($conf83) ?: []) : [];
        line('list.conf83', implode(',', array_values(array_diff($files, ['.', '..']))));
        foreach (array_slice(array_values(array_diff($files, ['.', '..'])), 0, 10) as $f) {
            $fp = rtrim($conf83, '/').'/'.$f;
            if (is_file($fp) && is_readable($fp) && filesize($fp) < 8000) {
                echo "---- BEGIN $fp ----\n".file_get_contents($fp)."\n---- END ----\n";
            }
        }
    }
    if (is_readable($ini82)) {
        echo "---- BEGIN $ini82 ----\n".file_get_contents($ini82)."\n---- END ----\n";
    }
    echo "OK\n";
    exit;
}

if ($action === 'fix') {
    $parent = '/opt/alt/php83/link';
    if (!is_writable($parent)) {
        line('error', 'link parent not writable');
        echo "FAIL\n";
        exit;
    }

    // Replace conf symlink/dir with a real writable directory we control
    if (file_exists($conf83) || is_link($conf83)) {
        [$c, $o] = run('rm -rf '.escapeshellarg($conf83));
        line('rm_conf', "code=$c out=$o");
    }
    [$c, $o] = run('mkdir -p '.escapeshellarg($conf83));
    line('mkdir_conf', "code=$c out=$o");
    if (!is_dir($conf83) || !is_writable($conf83)) {
        line('error', 'conf dir not writable after mkdir');
        echo "FAIL\n";
        exit;
    }

    $src = is_readable($ini82) ? (string) file_get_contents($ini82) : '';
    // Rewrite php82 module paths -> php83
    $body = str_replace('/opt/alt/php82/', '/opt/alt/php83/', $src);

    // Drop ioncube if missing on 8.3
    if (!is_file($modulesDir.'/ioncube_loader.so')) {
        $body = preg_replace('/;---ioncube_loader---\s*zend_extension=.+\n?/i', '', $body);
    }
    // Fix opcache path if present
    if (is_file($modulesDir.'/opcache.so')) {
        $body = preg_replace(
            '/zend_extension\s*=\s*.*opcache\.so/i',
            'zend_extension='.$modulesDir.'/opcache.so',
            $body
        );
    } else {
        $body = preg_replace('/;---opcache---\s*zend_extension=.+\n?/i', '', $body);
    }

    // Ensure required modules exist as extension= lines
    $required = ['bcmath', 'dom', 'fileinfo', 'intl', 'mbstring', 'pdo', 'mysqlnd', 'nd_mysqli', 'nd_pdo_mysql', 'phar', 'xmlreader', 'xmlwriter', 'xsl', 'zip', 'posix', 'soap', 'gd'];
    foreach ($required as $m) {
        if (!is_file($modulesDir.'/'.$m.'.so')) {
            line('missing_so.'.$m, 'yes');
            continue;
        }
        if (!preg_match('/^\s*extension\s*=\s*'.preg_quote($m, '/').'(\.so)?\s*$/mi', $body)) {
            $body .= "\n;---{$m}---\nextension={$m}.so\n";
        }
    }

    $ok = @file_put_contents($conf83.'/alt_php.ini', $body) !== false;
    line('write_alt_php', $ok ? 'ok' : 'FAIL');
    line('alt_php_len', (string) strlen($body));

    // Also drop per-extension ini files (belt and suspenders)
    foreach ($required as $m) {
        if (!is_file($modulesDir.'/'.$m.'.so')) {
            continue;
        }
        @file_put_contents($conf83.'/'.$m.'.ini', ";---{$m}---\nextension={$m}.so\n");
    }

    // Keep mysqli convenience if present
    if (is_file($modulesDir.'/mysqli.so')) {
        @file_put_contents($conf83.'/mysqli.ini', "extension=mysqli.so\n");
    }

    line('list.conf83', implode(',', array_values(array_diff(@scandir($conf83) ?: [], ['.', '..']))));

    // LiteSpeed keeps persistent lsphp workers that already scanned the old conf.
    // Restart this user's workers so the next request reloads extension inis.
    $restarts = [
        'pkill -u "$(whoami)" lsphp || true',
        'killall -u "$(whoami)" lsphp || true',
        'touch '.escapeshellarg(dirname($_SERVER['DOCUMENT_ROOT'] ?? '/home/gonulkop/public_html').'/.lsphp_restart') .' || true',
        'touch '.escapeshellarg(($_SERVER['DOCUMENT_ROOT'] ?? '').'/.lsphp_restart').' || true',
        'touch /home/gonulkop/.lsphp_restart || true',
    ];
    foreach ($restarts as $i => $cmd) {
        [$c, $o] = run($cmd);
        line('restart'.$i, "code=$c out=".str_replace("\n", ' | ', $o));
    }

    echo "---- alt_php.ini ----\n".$body."\n---- END ----\n";
    echo "OK\n";
    exit;
}

if ($action === 'restore') {
    $parent = '/opt/alt/php83/link';
    if (file_exists($conf83) || is_link($conf83)) {
        run('rm -rf '.escapeshellarg($conf83));
    }
    // Restore native layout used before our experiments
    [$c, $o] = run('ln -s /opt/alt/php83/etc/php.d '.escapeshellarg($conf83));
    line('restore_ln', "code=$c out=$o");
    $cfg = '/home/gonulkop/.cl.selector/defaults.cfg';
    $bak = $cfg.'.bak-laravel13';
    if (is_readable($bak)) {
        @file_put_contents($cfg, file_get_contents($bak));
        line('restore_cfg', 'ok');
    }
    echo "OK\n";
    exit;
}

echo "FAIL unknown\n";
