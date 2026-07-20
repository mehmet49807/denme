<?php
if (($_GET['key'] ?? '') !== 'gk-cpanel-setup-2026') {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$candidates = [
    dirname(__DIR__).'/admin.gonulkoprusu.com',
    '/home/gonulkop/admin.gonulkoprusu.com',
    dirname(__DIR__).'/gonulkoprusu.com',
    __DIR__,
];

$root = null;
foreach ($candidates as $candidate) {
    if (is_file($candidate.'/vendor/autoload.php') && is_file($candidate.'/bootstrap/app.php')) {
        $root = $candidate;
        break;
    }
}

if ($root === null) {
    http_response_code(500);
    exit("laravel root missing\n");
}

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "root=$root\n";

try {
    $column = Illuminate\Support\Facades\DB::selectOne("
        SELECT COLUMN_TYPE, DATA_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'role'
        LIMIT 1
    ");
    echo 'before='.json_encode($column)."\n";

    Illuminate\Support\Facades\DB::statement(
        "ALTER TABLE `users` MODIFY `role` VARCHAR(32) NOT NULL DEFAULT 'user'"
    );

    $after = Illuminate\Support\Facades\DB::selectOne("
        SELECT COLUMN_TYPE, DATA_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'role'
        LIMIT 1
    ");
    echo 'after='.json_encode($after)."\n";
    echo "OK staff role column widened\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'ERROR '.$e->getMessage()."\n";
}
