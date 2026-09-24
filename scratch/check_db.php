<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FuelLog;
use App\Models\Vehicle;

echo "=== FUEL LOGS BREAKDOWN BY FUEL TYPE ===\n";
$logs = FuelLog::select('fuel_type', \DB::raw('count(*) as cnt'), \DB::raw('sum(cost) as total_cost'), \DB::raw('sum(amount_liters) as total_amount'))
    ->groupBy('fuel_type')
    ->get();
print_r($logs->toArray());

echo "\n=== VEHICLE TYPES ===\n";
$vehicles = Vehicle::all(['id', 'make', 'model', 'type', 'license_plate']);
print_r($vehicles->toArray());
