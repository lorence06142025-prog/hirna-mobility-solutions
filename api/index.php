<?php

// Create required writable directories in /tmp for Vercel serverless environment
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

// Auto-create SQLite DB in /tmp for Vercel serverless demo deployment
$dbFile = '/tmp/database.sqlite';
$isNewDb = !file_exists($dbFile) || filesize($dbFile) === 0;
if (!file_exists($dbFile)) {
    touch($dbFile);
}

// Force SQLite database connection in all PHP superglobals for Vercel
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $dbFile;
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = $dbFile;
putenv("DB_CONNECTION=sqlite");
putenv("DB_DATABASE={$dbFile}");

// Run migration and seed BEFORE processing the HTTP request if DB is fresh
if ($isNewDb) {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $setupApp = require __DIR__ . '/../bootstrap/app.php';
        $setupApp->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        unset($setupApp);
    } catch (\Throwable $e) {
        // Silently pass
    }
}

// Forward serverless request to public/index.php
require __DIR__ . '/../public/index.php';
