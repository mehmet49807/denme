<?php
// Temporary script to fix users table columns — DELETE after use
$key = $_GET['key'] ?? '';
if (!hash_equals('gk-fix-columns-2026', $key)) {
    http_response_code(403);
    exit('403');
}

header('Content-Type: text/plain; charset=utf-8');

// Load Laravel database config
$base = dirname(__DIR__);
require $base.'/vendor/autoload.php';

$app = require_once $base.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting column fixes...\n";

try {
    DB::statement("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
    echo "✓ password -> nullable\n";
} catch (\Throwable $e) {
    echo "✗ password: ".$e->getMessage()."\n";
}

try {
    DB::statement("ALTER TABLE users MODIFY COLUMN city VARCHAR(100) NULL");
    echo "✓ city -> nullable\n";
} catch (\Throwable $e) {
    echo "✗ city: ".$e->getMessage()."\n";
}

try {
    DB::statement("ALTER TABLE users MODIFY COLUMN country VARCHAR(100) NULL");
    echo "✓ country -> nullable\n";
} catch (\Throwable $e) {
    echo "✗ country: ".$e->getMessage()."\n";
}

try {
    DB::statement("ALTER TABLE users MODIFY COLUMN district VARCHAR(100) NULL");
    echo "✓ district -> nullable\n";
} catch (\Throwable $e) {
    echo "✗ district: ".$e->getMessage()."\n";
}

echo "\nDone!\n";
