<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

User::query()->update(['last_otp_verified_at' => now()]);

echo "All users updated with last_otp_verified_at = " . now()->toDateTimeString() . "\n";
