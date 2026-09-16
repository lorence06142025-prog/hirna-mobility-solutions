<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostAnalysisController extends Controller
{
    /**
     * Display Transport Cost Analysis & Optimization (TCAO) Dashboard
     */
    public function index(Request $request)
    {
        $timeframe = $request->get('timeframe', 'month');

        // Total Fleet Metrics (Raw numbers for accurate calculations)
        $totalDistance = (float) Trip::where('status', 'completed')->sum('distance_km');
        $totalFuelCost = (float) FuelLog::sum('cost');
        $totalMaintenanceCost = (float) MaintenanceRecord::sum('cost');
        $totalOperationalCost = $totalFuelCost + $totalMaintenanceCost;

        // Raw float metrics for calculations
        $rawCostPerKm = $totalDistance > 0 ? ($totalOperationalCost / $totalDistance) : 0;
        $rawFuelCostPerKm = $totalDistance > 0 ? ($totalFuelCost / $totalDistance) : 0;
        $rawMaintCostPerKm = $totalDistance > 0 ? ($totalMaintenanceCost / $totalDistance) : 0;

        // Formatted metrics for display
        $costPerKm = number_format($rawCostPerKm, 2);
        $fuelCostPerKm = number_format($rawFuelCostPerKm, 2);
        $maintCostPerKm = number_format($rawMaintCostPerKm, 2);

        // Vehicle Cost Breakdown
        $vehicles = Vehicle::withCount(['trips' => function($q) {
            $q->where('status', 'completed');
        }])->get()->map(function ($vehicle) {
            $trips = Trip::where('vehicle_id', $vehicle->id)->where('status', 'completed');
            $distance = (float) $trips->sum('distance_km');
            $fuelCost = (float) FuelLog::where('vehicle_id', $vehicle->id)->sum('cost');
            $maintCost = (float) MaintenanceRecord::where('vehicle_id', $vehicle->id)->sum('cost');
            $totalCost = $fuelCost + $maintCost;

            return [
                'id' => $vehicle->id,
                'license_plate' => $vehicle->license_plate,
                'model' => $vehicle->make . ' ' . $vehicle->model,
                'type' => $vehicle->type,
                'trips_completed' => $vehicle->trips_count,
                'distance_km' => round($distance, 1),
                'fuel_cost' => round($fuelCost, 2),
                'maintenance_cost' => round($maintCost, 2),
                'total_cost' => round($totalCost, 2),
                'cost_per_km' => $distance > 0 ? round($totalCost / $distance, 2) : 0,
                'efficiency_score' => $distance > 0 ? max(50, min(100, round(100 - (($totalCost / $distance) * 2), 1))) : 85,
            ];
        })->sortByDesc('total_cost');

        // Driver Efficiency & Cost Analysis (Using performance_score column)
        $drivers = Driver::with('user')->get()->map(function ($driver) {
            $trips = Trip::where('driver_id', $driver->id)->where('status', 'completed');
            $distance = (float) $trips->sum('distance_km');
            $tripIds = $trips->pluck('id');
            $fuelCost = (float) FuelLog::whereIn('trip_id', $tripIds)->sum('cost');
            $totalDuration = (float) $trips->sum('actual_duration_minutes');
            $avgSpeed = $totalDuration > 0 ? round(($distance / ($totalDuration / 60)), 1) : 0;

            $perfScore = (float) ($driver->performance_score ?? 100);

            return [
                'id' => $driver->id,
                'name' => $driver->user ? $driver->user->name : 'Driver #' . $driver->id,
                'license' => $driver->license_number,
                'rating' => 4.8,
                'safety_score' => $perfScore,
                'total_distance' => round($distance, 1),
                'fuel_cost' => round($fuelCost, 2),
                'cost_per_km' => $distance > 0 ? round($fuelCost / $distance, 2) : 0,
                'efficiency_tier' => $perfScore >= 85 ? 'High Efficiency' : ($perfScore >= 70 ? 'Moderate' : 'Needs Training'),
            ];
        })->sortByDesc('safety_score');

        // AI Cost Optimization Suggestions
        $optimizationInsights = [];

        // Insight 1: Vehicle type comparison
        $threshold = $rawCostPerKm * 1.25;
        $highCostVehicles = $vehicles->filter(function($v) use ($threshold) {
            return $v['cost_per_km'] > $threshold;
        });

        if ($highCostVehicles->count() > 0) {
            $names = $highCostVehicles->pluck('license_plate')->implode(', ');
            $optimizationInsights[] = [
                'type' => 'warning',
                'title' => 'High Operational Cost Flagged',
                'description' => "Vehicles ({$names}) have a cost/km exceeding fleet average by over 25%. Scheduled maintenance or driver retraining is recommended.",
                'potential_savings' => '₱' . number_format($highCostVehicles->sum('total_cost') * 0.15, 2) . ' / month'
            ];
        }

        // Insight 2: Route & Fuel optimization
        if ($totalFuelCost > 0) {
            $optimizationInsights[] = [
                'type' => 'success',
                'title' => 'AI Eco-Routing Savings Opportunity',
                'description' => "Enforcing AI eco-routes on long-haul dispatches can reduce total EV energy charging costs by an estimated 11.4%.",
                'potential_savings' => '₱' . number_format($totalFuelCost * 0.114, 2) . ' / month'
            ];
        }

        return view('cost-analysis.index', compact(
            'totalDistance', 'totalFuelCost', 'totalMaintenanceCost', 'totalOperationalCost',
            'costPerKm', 'fuelCostPerKm', 'maintCostPerKm',
            'vehicles', 'drivers', 'optimizationInsights', 'timeframe'
        ));
    }

    /**
     * Export TCAO Fleet Cost Analysis report as CSV download
     */
    public function exportCsv(Request $request)
    {
        $fileName = 'TCAO_Cost_Analysis_' . date('Y-m-d_His') . '.csv';

        $vehicles = Vehicle::withCount(['trips' => function($q) {
            $q->where('status', 'completed');
        }])->get()->map(function ($vehicle) {
            $trips = Trip::where('vehicle_id', $vehicle->id)->where('status', 'completed');
            $distance = (float) $trips->sum('distance_km');
            $fuelCost = (float) FuelLog::where('vehicle_id', $vehicle->id)->sum('cost');
            $maintCost = (float) MaintenanceRecord::where('vehicle_id', $vehicle->id)->sum('cost');
            $totalCost = $fuelCost + $maintCost;

            return [
                'license_plate' => $vehicle->license_plate,
                'model' => $vehicle->make . ' ' . $vehicle->model,
                'type' => $vehicle->type,
                'trips_completed' => $vehicle->trips_count,
                'distance_km' => round($distance, 1),
                'fuel_cost' => round($fuelCost, 2),
                'maintenance_cost' => round($maintCost, 2),
                'total_cost' => round($totalCost, 2),
                'cost_per_km' => $distance > 0 ? round($totalCost / $distance, 2) : 0,
                'efficiency_score' => $distance > 0 ? max(50, min(100, round(100 - (($totalCost / $distance) * 2), 1))) : 85,
            ];
        });

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['License Plate', 'Model', 'Vehicle Type', 'Trips Completed', 'Distance (km)', 'Fuel Cost (PHP)', 'Maintenance Cost (PHP)', 'Total Cost (PHP)', 'Cost per KM (PHP)', 'Efficiency Score (%)'];

        $callback = function() use($vehicles, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($vehicles as $row) {
                fputcsv($file, [
                    $row['license_plate'],
                    $row['model'],
                    $row['type'],
                    $row['trips_completed'],
                    $row['distance_km'],
                    $row['fuel_cost'],
                    $row['maintenance_cost'],
                    $row['total_cost'],
                    $row['cost_per_km'],
                    $row['efficiency_score'] . '%'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Render printable PDF view for TCAO report
     */
    public function exportPdf(Request $request)
    {
        $totalDistance = (float) Trip::where('status', 'completed')->sum('distance_km');
        $totalFuelCost = (float) FuelLog::sum('cost');
        $totalMaintenanceCost = (float) MaintenanceRecord::sum('cost');
        $totalOperationalCost = $totalFuelCost + $totalMaintenanceCost;

        $costPerKm = number_format($totalDistance > 0 ? ($totalOperationalCost / $totalDistance) : 0, 2);
        $fuelCostPerKm = number_format($totalDistance > 0 ? ($totalFuelCost / $totalDistance) : 0, 2);
        $maintCostPerKm = number_format($totalDistance > 0 ? ($totalMaintenanceCost / $totalDistance) : 0, 2);

        $vehicles = Vehicle::all()->map(function ($vehicle) {
            $trips = Trip::where('vehicle_id', $vehicle->id)->where('status', 'completed');
            $distance = (float) $trips->sum('distance_km');
            $fuelCost = (float) FuelLog::where('vehicle_id', $vehicle->id)->sum('cost');
            $maintCost = (float) MaintenanceRecord::where('vehicle_id', $vehicle->id)->sum('cost');
            $totalCost = $fuelCost + $maintCost;

            return [
                'license_plate' => $vehicle->license_plate,
                'model' => $vehicle->make . ' ' . $vehicle->model,
                'distance_km' => round($distance, 1),
                'fuel_cost' => round($fuelCost, 2),
                'maintenance_cost' => round($maintCost, 2),
                'total_cost' => round($totalCost, 2),
                'cost_per_km' => $distance > 0 ? round($totalCost / $distance, 2) : 0,
            ];
        });

        return view('cost-analysis.pdf', compact(
            'totalDistance', 'totalFuelCost', 'totalMaintenanceCost', 'totalOperationalCost',
            'costPerKm', 'fuelCostPerKm', 'maintCostPerKm', 'vehicles'
        ));
    }
}

