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
$cfgPath = '/home/gonulkop/.cl.selector/defaults.cfg';
$need = ['mbstring', 'pdo', 'pdo_mysql', 'fileinfo', 'zip', 'phar', 'intl', 'bcmath'];

function line(string $k, string $v): void
{
    echo $k.'='.$v."\n";
}

function try_run(string $cmd): array
{
    $out = '';
    $code = -1;
    $method = 'none';
    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
    foreach (['exec', 'passthru', 'proc_open', 'popen'] as $fn) {
        if (!function_exists($fn) || in_array($fn, $disabled, true)) {
            continue;
        }
        $method = $fn;
        if ($fn === 'exec') {
            $lines = [];
            @exec($cmd.' 2>&1', $lines, $code);
            $out = implode("\n", $lines);
            break;
        }
        if ($fn === 'passthru') {
            ob_start();
            @passthru($cmd.' 2>&1', $code);
            $out = (string) ob_get_clean();
            break;
        }
        if ($fn === 'popen') {
            $h = @popen($cmd.' 2>&1', 'r');
            if ($h) {
                $out = (string) stream_get_contents($h);
                $code = pclose($h);
            }
            break;
        }
        if ($fn === 'proc_open') {
            $desc = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $proc = @proc_open($cmd, $desc, $pipes);
            if (is_resource($proc)) {
                $out = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $code = proc_close($proc);
            }
            break;
        }
    }

    return compact('method', 'out', 'code');
}

function dump_exts(array $need): void
{
    foreach ($need as $ext) {
        line('ext.'.$ext, extension_loaded($ext) ? 'yes' : 'NO');
    }
}

line('PHP_VERSION', PHP_VERSION);
line('SAPI', PHP_SAPI);
line('INI_SCANNED', (string) (php_ini_scanned_files() ?: ''));
dump_exts($need);

if ($action === 'info') {
    if (is_readable($cfgPath) && preg_match('/^php\s*=\s*(.+)$/m', (string) file_get_contents($cfgPath), $m)) {
        line('selector.php', trim($m[1]));
    }
    foreach ([
        '/opt/alt/php82/link',
        '/opt/alt/php82/link/conf',
        '/opt/alt/php83/link',
        '/opt/alt/php83/link/conf',
        '/etc/cl.php.d',
        '/etc/cl.php.d/alt-php82',
        '/etc/cl.php.d/alt-php83',
    ] as $p) {
        $exists = file_exists($p) || is_dir($p) || is_link($p);
        $w = $exists && is_writable($p);
        $link = is_link($p) ? (' -> '.readlink($p)) : '';
        line('path.'.$p, ($exists ? 'exists' : 'missing').';w='.($w ? '1' : '0').$link);
        if (is_dir($p) && is_readable($p)) {
            $files = @scandir($p) ?: [];
            line('list.'.$p, implode(',', array_slice(array_values(array_diff($files, ['.', '..'])), 0, 80)));
        }
    }
    echo "OK\n";
    exit;
}

if ($action === 'switch83') {
    // 1) Update defaults.cfg
    if (is_readable($cfgPath) && is_writable($cfgPath)) {
        $cfg = (string) file_get_contents($cfgPath);
        $backup = $cfgPath.'.bak-laravel13';
        if (!is_file($backup)) {
            @file_put_contents($backup, $cfg);
        }
        $new = preg_replace('/^(php\s*=\s*)(.+)$/m', '${1}8.3', $cfg, 1);
        @file_put_contents($cfgPath, $new);
        line('defaults', 'php=8.3');
    }

    $cmds = [
        'selectorctl --current -i php',
        'selectorctl --user-current -i php',
        'selectorctl --list -i php',
        'selectorctl --list-user-extensions -i php -v 8.3 -a',
        'selectorctl --set-user-current -i php -v 8.3 -p',
        'selectorctl -b -i php -v 8.3 -p',
        'selectorctl --enable-user-extensions=mbstring,pdo,pdo_mysql,fileinfo,zip,phar,intl,bcmath,mysqlnd,nd_mysqli,nd_pdo_mysql,dom,xmlreader,xmlwriter,xsl,posix,soap,gd,opcache -i php -v 8.3',
        'selectorctl -e mbstring,pdo,pdo_mysql,fileinfo,zip,phar,intl,bcmath,mysqlnd,nd_mysqli,nd_pdo_mysql -i php -v 8.3',
        'ls -la /opt/alt/php83/link /opt/alt/php83/link/conf /etc/cl.php.d 2>&1',
        'ls -la /etc/cl.php.d/alt-php83 2>&1 | head -50',
        'ls -la /etc/cl.php.d/alt-php82 2>&1 | head -20',
        'readlink -f /opt/alt/php82/link/conf; readlink -f /opt/alt/php83/link/conf; ls -la /opt/alt/php83/link/ 2>&1',
    ];

    foreach ($cmds as $i => $cmd) {
        $r = try_run($cmd);
        line('cmd'.$i.'.code', (string) $r['code']);
        echo "---- CMD: $cmd ----\n".$r['out']."\n---- END ----\n";
    }

    // 2) Manual symlink fix if selector left php83 conf incomplete
    $target = '/etc/cl.php.d/alt-php83';
    $linkParent = '/opt/alt/php83/link';
    $conf = '/opt/alt/php83/link/conf';
    if (is_dir($target) && is_writable($linkParent)) {
        // Match php82 layout: conf -> /etc/cl.php.d/alt-phpXX
        if (is_link($conf) || is_dir($conf) || is_file($conf)) {
            // Try rename aside then symlink
            $bak = $conf.'.bak-native';
            if (!file_exists($bak)) {
                @rename($conf, $bak);
                line('rename_conf', file_exists($bak) ? 'ok' : 'FAIL');
            } else {
                // remove empty-ish dir if needed
                if (is_dir($conf) && !is_link($conf)) {
                    foreach (@scandir($conf) ?: [] as $f) {
                        if ($f === '.' || $f === '..') {
                            continue;
                        }
                        @unlink($conf.'/'.$f);
                    }
                    @rmdir($conf);
                } elseif (is_link($conf) || is_file($conf)) {
                    @unlink($conf);
                }
            }
        }
        if (!file_exists($conf)) {
            $ok = @symlink($target, $conf);
            line('symlink_conf', $ok ? 'ok' : 'FAIL');
        } else {
            line('symlink_conf', 'still_exists:'.(is_link($conf) ? readlink($conf) : 'not_link'));
        }
    } else {
        line('manual_link', 'skip target='.(is_dir($target) ? 'yes' : 'no').' parent_w='.(is_writable($linkParent) ? '1' : '0'));
    }

    clearstatcache(true);
    line('conf83_link', is_link($conf) ? ('link:'.readlink($conf)) : (is_dir($conf) ? 'dir' : 'missing'));
    line('INI_SCANNED_NOW', (string) (php_ini_scanned_files() ?: ''));
    dump_exts($need);
    echo "OK\n";
    exit;
}

if ($action === 'restore82') {
    $backup = $cfgPath.'.bak-laravel13';
    if (is_readable($backup)) {
        @file_put_contents($cfgPath, file_get_contents($backup));
        line('restore_cfg', 'backup');
    } else {
        $cfg = (string) @file_get_contents($cfgPath);
        @file_put_contents($cfgPath, preg_replace('/^(php\s*=\s*)(.+)$/m', '${1}8.2', $cfg, 1));
        line('restore_cfg', 'forced');
    }
    foreach ([
        'selectorctl --set-user-current -i php -v 8.2 -p',
        'selectorctl -b -i php -v 8.2 -p',
    ] as $i => $cmd) {
        $r = try_run($cmd);
        echo "---- CMD: $cmd ----\n".$r['out']."\n---- END ----\n";
    }
    // restore php83 conf if we moved it
    $conf = '/opt/alt/php83/link/conf';
    $bak = $conf.'.bak-native';
    if (is_link($conf) && readlink($conf) === '/etc/cl.php.d/alt-php83') {
        @unlink($conf);
        if (file_exists($bak)) {
            @rename($bak, $conf);
            line('restore_conf', 'renamed_back');
        }
    }
    echo "OK\n";
    exit;
}

line('error', 'unknown action');
echo "FAIL\n";
