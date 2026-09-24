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
    $tmpBootstrap = '/tmp/bootstrap';
    
    foreach ([
        $tmpStorage . '/framework/views',
        $tmpStorage . '/framework/sessions',
        $tmpStorage . '/framework/cache/data',
        $tmpStorage . '/logs',
        $tmpBootstrap . '/cache',
    ] as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    // 2. Load Composer Autoloader & Create Application Instance
    require_once __DIR__ . '/../vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require __DIR__ . '/../bootstrap/app.php';

    // Set storage & bootstrap paths to writable /tmp directories in serverless environment
    $app->useStoragePath($tmpStorage);
    $app->useBootstrapPath($tmpBootstrap);

    // 3. Capture HTTP Request and bind into container BEFORE Kernel bootstrap
    $request = Request::capture();
    $app->instance('request', $request);

    // 4. Bootstrap HTTP Kernel so Facades and base bindings are active
    $kernel = $app->make(Kernel::class);
    $kernel->bootstrap();

    // 5. Test and validate the configured PostgreSQL connection.
    try {
        $currentDefault = config('database.default');
        $connDriver = config("database.connections.{$currentDefault}.driver", $currentDefault);
        
        if ($connDriver === 'pgsql' && !extension_loaded('pdo_pgsql')) {
            throw new \RuntimeException("The pdo_pgsql PHP extension is not installed in this serverless runtime.");
        }

        \Illuminate\Support\Facades\DB::connection()->getPdo();
    } catch (\Throwable $e) {
        throw new \RuntimeException('Configured Supabase PostgreSQL connection failed.', 0, $e);
    }

    // 6. Handle the serverless HTTP request and send the response.
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
