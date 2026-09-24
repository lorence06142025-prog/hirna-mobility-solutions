<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\FuelLog;
use App\Models\Trip;
use App\Models\VehicleReservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Universal CSV File Import Handler for All Dashboard Modules
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5048',
            'module_type' => 'required|string',
        ]);

        $file = $request->file('csv_file');
        $module = $request->input('module_type');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // skip header row

        $importedCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;

            try {
                switch ($module) {
                    case 'vehicles':
                        // Columns: Make, Model, License Plate, Year, Category, Battery Storage
                        if (isset($row[2]) || isset($row[1])) {
                            $plate = trim($row[2] ?? $row[1]);
                            Vehicle::updateOrCreate(
                                ['license_plate' => $plate],
                                [
                                    'make' => trim($row[0] ?? 'Hirna'),
                                    'model' => trim($row[1] ?? 'Nerio Green'),
                                    'year' => intval($row[3] ?? 2026),
                                    'type' => trim($row[4] ?? 'Sedan'),
                                    'fuel_capacity' => floatval($row[5] ?? 42.0),
                                    'status' => 'active',
                                ]
                            );
                            $importedCount++;
                        }
                        break;

                    case 'fuel':
                        // Columns: Date, Vehicle Plate, kWh Added, Cost PHP, Odometer, Type
                        $vehicle = Vehicle::where('license_plate', trim($row[1] ?? ''))->first() ?? Vehicle::first();
                        if ($vehicle) {
                            FuelLog::create([
                                'vehicle_id' => $vehicle->id,
                                'date' => trim($row[0] ?? now()->toDateString()),
                                'amount_liters' => floatval($row[2] ?? 35.0),
                                'cost' => floatval($row[3] ?? 402.50),
                                'odometer_reading' => floatval($row[4] ?? 12500),
                                'fuel_type' => trim($row[5] ?? 'Fast DC Charging'),
                            ]);
                            $importedCount++;
                        }
                        break;

                    case 'trips':
                        // Columns: Start Location, End Location, Distance, Fuel, Duration
                        Trip::create([
                            'start_location' => trim($row[0] ?? 'Manila Hub'),
                            'end_location' => trim($row[1] ?? 'Makati Hub'),
                            'start_lat' => 14.5995,
                            'start_lng' => 120.9842,
                            'end_lat' => 14.5547,
                            'end_lng' => 121.0244,
                            'distance_km' => floatval($row[2] ?? 14.5),
                            'estimated_fuel_liters' => floatval($row[3] ?? 2.4),
                            'estimated_duration_minutes' => intval($row[4] ?? 30),
                            'status' => 'scheduled',
                        ]);
                        $importedCount++;
                        break;

                    case 'reservations':
                        // Columns: Title/Purpose, Vehicle ID/Plate, Start Date, End Date
                        $vehicle = Vehicle::first();
                        $requestedBy = User::where('role', 'admin')->first();
                        if (!$vehicle || !$requestedBy) {
                            break;
                        }

                        $start = Carbon::parse($row[2] ?? now());
                        $end = Carbon::parse($row[3] ?? now()->addHours(2));
                        VehicleReservation::create([
                            'purpose' => trim($row[0] ?? 'Executive Dispatch'),
                            'vehicle_id' => $vehicle->id,
                            'requested_by' => session('user_id', $requestedBy->id),
                            'reservation_date' => $start->toDateString(),
                            'start_time' => $start->toTimeString(),
                            'end_time' => $end->toTimeString(),
                            'status' => 'approved',
                        ]);
                        $importedCount++;
                        break;
                }
            } catch (\Exception $e) {
                // Continue parsing remaining rows
                continue;
            }
        }

        fclose($handle);

        return redirect()->back()->with('success', "CSV Import Complete! Successfully imported {$importedCount} records into system.");
    }
}
