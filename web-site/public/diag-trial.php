<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username', 'rida')->first();
if (!$user) { echo "User not found"; exit; }

echo "username: {$user->username}\n";
echo "gender: {$user->gender}\n";
echo "trial_ends_at: " . ($user->trial_ends_at ?? 'NULL') . "\n";
echo "isOnTrial: " . ($user->isOnTrial() ? 'YES' : 'NO') . "\n";
echo "isPremium: " . ($user->isPremium() ? 'YES' : 'NO') . "\n";
echo "activePackageType: " . ($user->activePackageType() ?? 'NULL') . "\n";
echo "showsPremiumMemberBadge: " . ($user->showsPremiumMemberBadge() ? 'YES' : 'NO') . "\n";
echo "showsTrialBadge: " . ($user->showsTrialBadge() ? 'YES' : 'NO') . "\n";
echo "now: " . now() . "\n";
