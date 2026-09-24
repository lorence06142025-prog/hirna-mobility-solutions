<?php

/**
 * Hirna Mobility Solutions - Vercel Serverless Application Entry Point
 * Resilient DB connection bootstrap for Vercel Serverless.
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

// 3. Test & Validate Active Database Connection
$dbConnected = false;
try {
    $currentDefault = config('database.default');
    $connDriver = config("database.connections.{$currentDefault}.driver", $currentDefault);
    
    if ($connDriver === 'pgsql' && !extension_loaded('pdo_pgsql')) {
        throw new \RuntimeException("The pdo_pgsql PHP extension is not installed in this serverless runtime.");
    }

    \Illuminate\Support\Facades\DB::connection()->getPdo();
    $dbConnected = true;
    config(['cache.default' => 'database']);
} catch (\Throwable $e) {
    // If primary DB connection fails (missing driver or unreachable DB), fallback safely to /tmp SQLite
    \Illuminate\Support\Facades\Log::warning("PRIMARY DB CONNECT FAILED: " . $e->getMessage() . ". Falling back to local SQLite.");
    
    $dbFile = '/tmp/database.sqlite';
    if (!file_exists($dbFile)) {
        @touch($dbFile);
    }
    
    config(['database.default' => 'sqlite']);
    config(['database.connections.sqlite.database' => $dbFile]);
    config(['cache.default' => 'array']);
}

// 4. Safe Non-Destructive Schema Migration Bootstrapping
try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    
    if (\Illuminate\Support\Facades\Schema::hasTable('users') && \App\Models\User::count() === 0) {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    }
} catch (\Throwable $e) {
    \Illuminate\Support\Facades\Log::error("DB MIGRATION BOOTSTRAP EXCEPTION: " . $e->getMessage());
}

// 5. Forward Serverless Request to public/index.php
require __DIR__ . '/../public/index.php';
