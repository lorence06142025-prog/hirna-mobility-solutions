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

// Detect if External Cloud DB (Supabase / Postgres / MySQL) is configured via Vercel Environment Variables
$externalConn = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? null);
$externalHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? null);

$isExternalDb = ($externalConn && $externalConn !== 'sqlite') || !empty($externalHost);

if (!$isExternalDb) {
    // Fallback SQLite DB setup for local/demo serverless deployment
    $sourceDb = __DIR__ . '/../database/database.sqlite';
    $dbFile = '/tmp/database.sqlite';
    $isNewDb = false;

    if (!file_exists($dbFile) || filesize($dbFile) === 0) {
        if (file_exists($sourceDb) && filesize($sourceDb) > 0) {
            @copy($sourceDb, $dbFile);
        } else {
            @touch($dbFile);
            $isNewDb = true;
        }
    }

    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_ENV['DB_DATABASE'] = $dbFile;
    $_SERVER['DB_CONNECTION'] = 'sqlite';
    $_SERVER['DB_DATABASE'] = $dbFile;
    putenv("DB_CONNECTION=sqlite");
    putenv("DB_DATABASE={$dbFile}");

    if ($isNewDb) {
        try {
            require_once __DIR__ . '/../vendor/autoload.php';
            $setupApp = require __DIR__ . '/../bootstrap/app.php';
            $setupApp->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            if (\Illuminate\Support\Facades\Schema::hasTable('users') && \App\Models\User::count() === 0) {
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            }
            unset($setupApp);
        } catch (\Throwable $e) {
            // Silently pass
        }
    }
} else {
    // Run safe migrations & initial seed on external DB (Supabase) if tables do not exist yet
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $setupApp = require __DIR__ . '/../bootstrap/app.php';
        $setupApp->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        if (\Illuminate\Support\Facades\Schema::hasTable('users') && \App\Models\User::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        }
        unset($setupApp);
    } catch (\Throwable $e) {
        // Silently pass
    }
}

// Forward serverless request to public/index.php
require __DIR__ . '/../public/index.php';
