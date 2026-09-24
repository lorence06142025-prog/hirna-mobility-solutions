<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MaintenanceRecord;

echo "=== ALL MAINTENANCE RECORDS IN DB ===\n";
$records = MaintenanceRecord::with('vehicle')->get();
print_r($records->toArray());

echo "\n=== QUERY TEST ===\n";
$test1 = MaintenanceRecord::where('status', 'scheduled')->get();
echo "Status = scheduled count: " . count($test1) . "\n";

$test2 = MaintenanceRecord::all();
echo "Total maintenance records count: " . count($test2) . "\n";
