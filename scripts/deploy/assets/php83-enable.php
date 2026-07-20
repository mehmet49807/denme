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

    foreach (['exec', 'passthru', 'shell_exec', 'system', 'proc_open', 'popen'] as $fn) {
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
        if ($fn === 'shell_exec') {
            $out = (string) @shell_exec($cmd.' 2>&1');
            $code = 0;
            break;
        }
        if ($fn === 'system') {
            ob_start();
            @system($cmd.' 2>&1', $code);
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

line('PHP_VERSION', PHP_VERSION);
line('SAPI', PHP_SAPI);
line('INI_SCANNED', (string) (php_ini_scanned_files() ?: ''));
line('disable_functions', (string) ini_get('disable_functions'));

$need = ['mbstring', 'pdo', 'pdo_mysql', 'fileinfo', 'zip', 'phar', 'intl', 'bcmath'];
foreach ($need as $ext) {
    line('ext.'.$ext, extension_loaded($ext) ? 'yes' : 'NO');
}

if ($action === 'info') {
    if (is_readable($cfgPath)) {
        $cfg = (string) file_get_contents($cfgPath);
        if (preg_match('/^php\s*=\s*(.+)$/m', $cfg, $m)) {
            line('selector.php', trim($m[1]));
        }
        line('cfg_writable', is_writable($cfgPath) ? 'yes' : 'NO');
    }
    foreach (['/opt/alt/php82/link/conf', '/opt/alt/php83/link/conf'] as $d) {
        $files = is_dir($d) ? (@scandir($d) ?: []) : [];
        line('conf.'.$d, implode(',', array_slice(array_values(array_diff($files, ['.', '..'])), 0, 60)));
    }
    // Which exec-like functions are available?
    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
    foreach (['exec', 'passthru', 'shell_exec', 'system', 'proc_open', 'popen'] as $fn) {
        $ok = function_exists($fn) && !in_array($fn, $disabled, true);
        line('fn.'.$fn, $ok ? 'yes' : 'NO');
    }
    echo "OK\n";
    exit;
}

if ($action === 'switch83') {
    if (!is_readable($cfgPath) || !is_writable($cfgPath)) {
        line('error', 'defaults.cfg not writable');
        echo "FAIL\n";
        exit;
    }

    $backup = $cfgPath.'.bak-laravel13';
    $cfg = (string) file_get_contents($cfgPath);
    if (!is_file($backup)) {
        @file_put_contents($backup, $cfg);
        line('backup', 'written');
    }

    $new = preg_replace('/^(php\s*=\s*)(.+)$/m', '${1}8.3', $cfg, 1, $count);
    if ($count < 1) {
        line('error', 'php= line not found');
        echo "FAIL\n";
        exit;
    }
    $ok = @file_put_contents($cfgPath, $new) !== false;
    line('write_defaults', $ok ? 'ok' : 'FAIL');
    if (preg_match('/^php\s*=\s*(.+)$/m', (string) file_get_contents($cfgPath), $m)) {
        line('selector.php', trim($m[1]));
    }

    $cmds = [
        'selectorctl --interpreter=php --version=8.3 2>&1',
        'selectorctl --user=gonulkop --interpreter=php --select=8.3 2>&1',
        '/usr/bin/selectorctl --interpreter=php --version=8.3 2>&1',
        '/usr/sbin/cagefsctl --force-update 2>&1',
        'cloudlinux-selector set --json --interpreter=php --user=gonulkop --selector-version=8.3 2>&1',
        'which selectorctl; which cagefsctl; which cloudlinux-selector 2>&1',
        'ls -la /opt/alt/php83/link/conf 2>&1',
        'ls -la /opt/alt/php82/link/conf 2>&1 | head -40',
    ];

    foreach ($cmds as $i => $cmd) {
        $r = try_run($cmd);
        line('cmd'.$i.'.method', $r['method']);
        line('cmd'.$i.'.code', (string) $r['code']);
        echo "---- CMD: $cmd ----\n".$r['out']."\n---- END ----\n";
    }

    clearstatcache(true);
    line('conf83', implode(',', array_diff(@scandir('/opt/alt/php83/link/conf') ?: [], ['.', '..'])));
    foreach ($need as $ext) {
        line('ext.'.$ext, extension_loaded($ext) ? 'yes' : 'NO');
    }
    echo "OK\n";
    exit;
}

if ($action === 'restore82') {
    $backup = $cfgPath.'.bak-laravel13';
    if (is_readable($backup)) {
        @file_put_contents($cfgPath, file_get_contents($backup));
        line('restore', 'from_backup');
    } else {
        $cfg = (string) file_get_contents($cfgPath);
        $new = preg_replace('/^(php\s*=\s*)(.+)$/m', '${1}8.2', $cfg, 1);
        @file_put_contents($cfgPath, $new);
        line('restore', 'forced_8.2');
    }
    if (preg_match('/^php\s*=\s*(.+)$/m', (string) file_get_contents($cfgPath), $m)) {
        line('selector.php', trim($m[1]));
    }
    foreach ([
        'selectorctl --interpreter=php --version=8.2 2>&1',
        'selectorctl --user=gonulkop --interpreter=php --select=8.2 2>&1',
    ] as $i => $cmd) {
        $r = try_run($cmd);
        line('restore_cmd'.$i.'.method', $r['method']);
        echo "---- CMD: $cmd ----\n".$r['out']."\n---- END ----\n";
    }
    echo "OK\n";
    exit;
}

line('error', 'unknown action');
echo "FAIL\n";
