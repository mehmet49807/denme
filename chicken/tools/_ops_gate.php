<?php

declare(strict_types=1);

/**
 * Shared gate for emergency tools. Requires ops_secret from config/env.
 */
require dirname(__DIR__) . '/app/helpers.php';

header('Content-Type: text/plain; charset=utf-8');

$provided = (string) ($_GET['key'] ?? $_SERVER['HTTP_X_OPS_KEY'] ?? '');
$secret = ops_secret();
if ($secret === '' || !hash_equals($secret, $provided)) {
    http_response_code(403);
    echo "forbidden\n";
    exit;
}
