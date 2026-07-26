<?php

declare(strict_types=1);

/**
 * Temporary server-side bootstrap: copy chicken app into subdomain docroot if visible.
 * Delete after use.
 */
header('Content-Type: text/plain; charset=utf-8');

$key = $_GET['key'] ?? '';
$expected = $_GET['expect'] ?? '';
if ($expected === '' || !hash_equals($expected, $key)) {
    http_response_code(403);
    echo "forbidden\n";
    exit;
}

$source = realpath(__DIR__ . '/..');
if ($source === false) {
    echo "source missing\n";
    exit;
}

echo "source={$source}\n";
echo "php_uname=" . php_uname() . "\n";
echo "cwd=" . getcwd() . "\n";
echo "document_root=" . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";

$home = dirname($source, 3); // likely /home/USER or similar depending on nesting
$candidates = [
    $source, // already in a chicken folder
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../chicken.gonulkoprusu.com',
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../../chicken.gonulkoprusu.com',
    '/home/gonulkop/chicken.gonulkoprusu.com',
    '/home/gonulkoprusu/chicken.gonulkoprusu.com',
    '/home/gonulkop/public_html/../chicken.gonulkoprusu.com',
];

// Discover nearby directories
$probeRoots = array_unique(array_filter([
    dirname($source),
    dirname($source, 2),
    dirname($source, 3),
    dirname($source, 4),
    $_SERVER['DOCUMENT_ROOT'] ?? null,
    dirname((string) ($_SERVER['DOCUMENT_ROOT'] ?? '')),
    dirname((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), 2),
]));

foreach ($probeRoots as $root) {
    if (!$root || !is_dir($root)) {
        continue;
    }
    echo "listing {$root}\n";
    foreach (scandir($root) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $root . DIRECTORY_SEPARATOR . $entry;
        if (is_dir($full) && stripos($entry, 'chicken') !== false) {
            echo "found_dir {$full}\n";
            $candidates[] = $full;
            $pub = $full . '/public_html';
            if (is_dir($pub)) {
                echo "found_dir {$pub}\n";
                $candidates[] = $pub;
            }
        }
    }
}

$candidates = array_values(array_unique(array_filter($candidates)));
$target = null;
foreach ($candidates as $candidate) {
    $real = realpath($candidate) ?: $candidate;
    if (!is_dir($real)) {
        continue;
    }
    // Prefer dedicated domain folder, not the source itself
    if (realpath($real) === $source) {
        continue;
    }
    if (stripos($real, 'chicken.gonulkoprusu.com') !== false) {
        $target = $real;
        break;
    }
}

if ($target === null) {
    echo "NO_TARGET\n";
    echo "open_basedir=" . (ini_get('open_basedir') ?: '(none)') . "\n";
    exit;
}

echo "target={$target}\n";

function copy_tree(string $src, string $dst): int
{
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $rel = substr($item->getPathname(), strlen($src) + 1);
        if ($rel === false) {
            continue;
        }
        if (str_contains($rel, 'tools/server_bootstrap.php')) {
            continue;
        }
        $to = $dst . DIRECTORY_SEPARATOR . $rel;
        if ($item->isDir()) {
            if (!is_dir($to) && !mkdir($to, 0755, true) && !is_dir($to)) {
                throw new RuntimeException('mkdir failed: ' . $to);
            }
            continue;
        }
        $dir = dirname($to);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('mkdir failed: ' . $dir);
        }
        if (!copy($item->getPathname(), $to)) {
            throw new RuntimeException('copy failed: ' . $to);
        }
        $count++;
    }
    return $count;
}

try {
    $copied = copy_tree($source, $target);
    echo "COPIED={$copied}\n";
    echo "OK\n";
} catch (Throwable $e) {
    echo "ERROR=" . $e->getMessage() . "\n";
}
