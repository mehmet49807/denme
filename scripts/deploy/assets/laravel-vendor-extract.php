<?php
/**
 * Standalone vendor bundle extractor (Laravel yüklenmeden çalışır).
 * GET ?key=gk-laravel-update-2026
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'gk-laravel-update-2026' && $key !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'forbidden']);
    exit;
}

@set_time_limit(600);
@ini_set('max_execution_time', '600');
@ini_set('memory_limit', '512M');

$base = __DIR__;
if (! is_dir($base.'/app') && is_dir(dirname($base).'/app')) {
    $base = dirname($base);
}

$candidates = [
    $base.'/storage/app/laravel-vendor.zip',
    $base.'/storage/app/laravel-vendor.tgz',
    $base.'/laravel-vendor.zip',
];

$bundle = null;
foreach ($candidates as $path) {
    if (is_file($path)) {
        $bundle = $path;
        break;
    }
}

if ($bundle === null) {
    echo json_encode(['ok' => false, 'message' => 'bundle missing', 'base' => $base]);
    exit;
}

/**
 * Minimal stored-method ZIP extractor (no ZipArchive / Phar required).
 */
function gk_unzip(string $zipPath, string $dest): bool
{
    $fp = fopen($zipPath, 'rb');
    if ($fp === false) {
        return false;
    }
    $size = filesize($zipPath) ?: 0;
    if ($size < 30) {
        fclose($fp);
        return false;
    }

    // Find End of Central Directory
    $eoecd = null;
    $read = min($size, 65557);
    fseek($fp, -$read, SEEK_END);
    $tail = fread($fp, $read);
    $pos = strrpos($tail, "\x50\x4b\x05\x06");
    if ($pos === false) {
        fclose($fp);
        return false;
    }
    $ecd = substr($tail, $pos);
    if (strlen($ecd) < 22) {
        fclose($fp);
        return false;
    }
    $data = unpack('vdisk/vdiskStart/vdiskEntries/ventries/Vsize/Voffset/vcomment', substr($ecd, 4));
    $offset = (int) $data['offset'];
    $entries = (int) $data['entries'];

    for ($i = 0; $i < $entries; $i++) {
        fseek($fp, $offset);
        $header = fread($fp, 46);
        if (strlen($header) < 46 || substr($header, 0, 4) !== "\x50\x4b\x01\x02") {
            break;
        }
        $meta = unpack(
            'vverMade/vver/vflag/vmethod/vtime/vdate/Vcrc/Vcsize/Vsize/vnamelen/vexlen/vcomlen/vdisk/viattr/Veattr/Voffset',
            substr($header, 4)
        );
        $name = $meta['namelen'] > 0 ? fread($fp, $meta['namelen']) : '';
        if ($meta['exlen'] > 0) {
            fread($fp, $meta['exlen']);
        }
        if ($meta['comlen'] > 0) {
            fread($fp, $meta['comlen']);
        }
        $nextCentral = ftell($fp);

        if ($name === '' || str_ends_with($name, '/')) {
            if ($name !== '') {
                @mkdir($dest.'/'.rtrim($name, '/'), 0755, true);
            }
            $offset = $nextCentral;
            continue;
        }

        // Local file header
        fseek($fp, $meta['offset']);
        $local = fread($fp, 30);
        if (strlen($local) < 30 || substr($local, 0, 4) !== "\x50\x4b\x03\x04") {
            $offset = $nextCentral;
            continue;
        }
        $lmeta = unpack('vver/vflag/vmethod/vtime/vdate/Vcrc/Vcsize/Vsize/vnamelen/vexlen', substr($local, 4));
        if ($lmeta['namelen'] > 0) {
            fread($fp, $lmeta['namelen']);
        }
        if ($lmeta['exlen'] > 0) {
            fread($fp, $lmeta['exlen']);
        }

        $target = $dest.'/'.$name;
        $dir = dirname($target);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $csize = (int) $lmeta['csize'];
        $method = (int) $lmeta['method'];
        $payload = $csize > 0 ? fread($fp, $csize) : '';
        if ($method === 0) {
            file_put_contents($target, $payload);
        } elseif ($method === 8) {
            $raw = @gzinflate($payload);
            if ($raw === false) {
                fclose($fp);
                return false;
            }
            file_put_contents($target, $raw);
        } else {
            fclose($fp);
            return false;
        }

        $offset = $nextCentral;
    }

    fclose($fp);

    return true;
}

$ok = false;
$error = null;
$method = null;

if (str_ends_with($bundle, '.zip')) {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($bundle) === true) {
            $ok = $zip->extractTo($base);
            $zip->close();
            $method = 'ZipArchive';
            if (! $ok) {
                $error = 'ZipArchive extractTo failed';
            }
        } else {
            $error = 'ZipArchive open failed';
        }
    }
    if (! $ok) {
        $ok = gk_unzip($bundle, $base) && is_dir($base.'/vendor/laravel/framework');
        $method = $ok ? 'pure-php-unzip' : $method;
        if (! $ok && $error === null) {
            $error = 'pure-php unzip failed';
        }
    }
} else {
    try {
        if (class_exists('PharData')) {
            $phar = new PharData($bundle);
            $phar->extractTo($base, null, true);
            $ok = is_dir($base.'/vendor/laravel/framework');
            $method = 'PharData';
        } else {
            $error = 'PharData not available';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$version = null;
$versionFile = $base.'/vendor/laravel/framework/src/Illuminate/Foundation/Application.php';
if (is_readable($versionFile)) {
    $src = (string) file_get_contents($versionFile);
    if (preg_match("/const VERSION = '([^']+)'/", $src, $m)) {
        $version = $m[1];
    }
}

if ($ok) {
    @unlink($bundle);
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
}

echo json_encode([
    'ok' => $ok && $version !== null,
    'message' => $ok ? ('Vendor extracted · Laravel '.($version ?? '?').' via '.($method ?? '?')) : ('Extract failed: '.($error ?? 'unknown')),
    'version' => $version,
    'base' => $base,
    'bundle' => basename($bundle),
    'method' => $method,
    'php' => PHP_VERSION,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
