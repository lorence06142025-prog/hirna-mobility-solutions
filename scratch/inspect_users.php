<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== ALL USERS IN DATABASE ===\n";
$users = User::all();
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role} | Last OTP Verified: " . ($u->last_otp_verified_at ? $u->last_otp_verified_at->toDateTimeString() : 'NULL') . "\n";
}

echo "\nTotal DB Users count: " . count($users) . "\n";
