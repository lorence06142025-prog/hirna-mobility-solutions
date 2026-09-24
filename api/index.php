<?php

/**
 * Hirna Mobility Solutions - Vercel Serverless Application Entry Point
 * Ensures production data persistence with Supabase PostgreSQL.
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

// 2. Load Composer Autoloader & Bootstrap Laravel Environment to read Vercel env vars accurately
require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 3. Inspect Authoritative Database Configuration
$dbConn = config('database.default') ?: env('DB_CONNECTION');
$dbHost = config("database.connections.{$dbConn}.host") ?: env('DB_HOST');

$isPersistentDb = ($dbConn && strtolower($dbConn) !== 'sqlite') 
    || (!empty($dbHost) && strtolower($dbHost) !== '127.0.0.1' && strtolower($dbHost) !== 'localhost');

if (!$isPersistentDb) {
    // Fallback local SQLite setup ONLY if no cloud database (Supabase) is configured
    $dbFile = '/tmp/database.sqlite';
    if (!file_exists($dbFile)) {
        @touch($dbFile);
    }
    config(['database.default' => 'sqlite']);
    config(['database.connections.sqlite.database' => $dbFile]);
} else {
    // Force database cache store so RateLimiter state is shared across serverless workers in Supabase
    config(['cache.default' => 'database']);
}

// 4. Safe Non-Destructive Migration Bootstrapping
try {
    // Non-destructive schema migration (never drops existing tables or user records)
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    
    // Initial seed ONLY executed if database has 0 user records
    if (\Illuminate\Support\Facades\Schema::hasTable('users') && \App\Models\User::count() === 0) {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    }
} catch (\Throwable $e) {
    // Silently continue if database schema is already up to date
}

// 5. Forward Serverless Request to public/index.php
require __DIR__ . '/../public/index.php';
