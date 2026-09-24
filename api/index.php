<?php

/**
 * Hirna Mobility Solutions - Vercel Serverless Application Entry Point
 * Strictly enforces Supabase PostgreSQL Cloud Database for 100% production data persistence.
 */

// 1. Ensure required writable directories exist in /tmp for Vercel serverless environment
$tmpStorage = '/tmp/storage';
foreach ([
    $tmpStorage . '/framework/views',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/logs',
    '/tmp/bootstrap/cache',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// 2. Load Composer Autoloader & Bootstrap Laravel Environment
require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 3. Force Persistent Database Cache Driver for Shared RateLimiter State
config(['cache.default' => 'database']);

// 4. Safe Non-Destructive Schema Migration Bootstrapping on Supabase PostgreSQL
try {
    // Non-destructive schema migration (never drops existing tables or user records)
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    
    // Seed initial roles ONLY if database has 0 user records
    if (\Illuminate\Support\Facades\Schema::hasTable('users') && \App\Models\User::count() === 0) {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    }
} catch (\Throwable $e) {
    \Illuminate\Support\Facades\Log::error("SUPABASE DB BOOTSTRAP EXCEPTION: " . $e->getMessage());
}

// 5. Forward Serverless Request to public/index.php
require __DIR__ . '/../public/index.php';
