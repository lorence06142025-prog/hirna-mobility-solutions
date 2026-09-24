<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\RateLimiter;

$key = 'test_lockout_key_' . time();
$maxAttempts = 3;

echo "Initial attempts: " . RateLimiter::attempts($key) . "\n";
echo "Initial remaining: " . RateLimiter::remaining($key, $maxAttempts) . "\n\n";

for ($i = 1; $i <= 4; $i++) {
    echo "--- Attempt {$i} ---\n";
    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
        echo "LOCKED OUT! Available in: " . RateLimiter::availableIn($key) . " seconds\n";
    } else {
        RateLimiter::hit($key, 60);
        $remaining = RateLimiter::remaining($key, $maxAttempts);
        $attempts = RateLimiter::attempts($key);
        echo "Hit recorded. Total attempts: {$attempts} | Remaining: {$remaining}\n";
    }
}

RateLimiter::clear($key);
