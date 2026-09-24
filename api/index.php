<?php

/**
 * Hirna Mobility Solutions - Vercel Serverless Application Entry Point
 * Resilient DB connection & filesystem bootstrap for Vercel Serverless.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

try {
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

    /** @var \Illuminate\Foundation\Application $app */
    $app = require __DIR__ . '/../bootstrap/app.php';

    // Set storage path to writable /tmp directory in serverless environment
    $app->useStoragePath($tmpStorage);

    // Bootstrap HTTP Kernel so Facades (DB, Log, Artisan, Schema) are registered
    $kernel = $app->make(Kernel::class);
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

    // 5. Handle Serverless HTTP Request directly using the bootstrapped application instance
    $request = Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $fatalError) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>500 Server Error</title></head><body style='font-family:sans-serif;padding:2rem;'>";
    echo "<h2 style='color:#dc2626;'>500 Serverless Application Error</h2>";
    echo "<p><strong>Details:</strong> " . htmlspecialchars($fatalError->getMessage()) . "</p>";
    echo "<p><em>Location:</em> " . htmlspecialchars($fatalError->getFile()) . ":" . $fatalError->getLine() . "</p>";
    echo "</body></html>";
}
