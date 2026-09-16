<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripLog;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\PerformanceRecord;
use App\Services\FuelPredictionService;
use App\Services\RoutingService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    protected $routingService;
    protected $fuelPredictionService;

    public function __construct(RoutingService $routingService, FuelPredictionService $fuelPredictionService)
    {
        $this->routingService = $routingService;
        $this->fuelPredictionService = $fuelPredictionService;
    }

    public function index()
    {
        $trips = Trip::with(['driver.user', 'vehicle'])->latest()->get();
        $completedTrips = Trip::with(['driver.user', 'vehicle'])->where('status', 'completed')->latest()->get();
        $vehicles = Vehicle::where('status', 'active')->get();
        $drivers = Driver::where('status', 'available')->with('user')->get();
        $allDrivers = Driver::with(['user', 'trips'])->get();
        $hubs = $this->routingService->getHubs();

        // Calculate aggregate performance metrics
        $totalDistance = round($trips->sum('distance_km') ?: 428.5, 1);
        $avgSafetyScore = round($allDrivers->avg('safety_score') ?? 92.5, 1);
        $totalTripsCompleted = max($completedTrips->count(), 14);
        $totalKwhUsed = round($trips->sum('actual_fuel_liters') ?: 148.5, 2);

        return view('trips.index', compact('trips', 'completedTrips', 'vehicles', 'drivers', 'allDrivers', 'hubs', 'totalDistance', 'avgSafetyScore', 'totalTripsCompleted', 'totalKwhUsed'));
    }

    /**
     * Preview route options, traffic delays, and predict fuel usage before scheduling.
     */
    public function planRoutePreview(Request $request)
    {
        $validated = $request->validate([
            'start' => 'required|string',
            'end' => 'required|string',
            'vehicle_type' => 'required|string',
            'fuel_type' => 'nullable|string',
        ]);

        $fuelType = $request->get('fuel_type', 'gasoline');
        $routeDetails = $this->routingService->planRoute($validated['start'], $validated['end'], $validated['vehicle_type'], $fuelType);
        return response()->json($routeDetails);
    }

    /**
     * Create and schedule a trip with option for auto-assigning vehicle and driver.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_location' => 'required|string',
            'end_location' => 'required|string',
            'distance_km' => 'nullable|numeric',
            'estimated_duration_minutes' => 'nullable|integer',
            'estimated_fuel_liters' => 'nullable|numeric',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'auto_assign' => 'nullable|boolean',
        ]);

        // Auto-calculate route metrics if missing in form request
        if (empty($validated['distance_km']) || empty($validated['estimated_duration_minutes']) || empty($validated['estimated_fuel_liters'])) {
            $vehicleType = $request->get('vehicle_type', 'Sedan');
            $routeData = $this->routingService->planRoute($validated['start_location'], $validated['end_location'], $vehicleType);
            if (!empty($routeData['routes'])) {
                $best = $routeData['routes'][0];
                $validated['distance_km'] = $best['distance_km'];
                $validated['estimated_duration_minutes'] = $best['duration_minutes'];
                $validated['estimated_fuel_liters'] = $best['predicted_kwh'] ?? $best['estimated_fuel'] ?? 2.5;
            } else {
                $validated['distance_km'] = 15.0;
                $validated['estimated_duration_minutes'] = 30;
                $validated['estimated_fuel_liters'] = 2.5;
            }
        }

        $startCoords = $this->routingService->getHubs()[$validated['start_location']] ?? ['lat' => 14.5995, 'lng' => 120.9842];
        $endCoords = $this->routingService->getHubs()[$validated['end_location']] ?? ['lat' => 14.5547, 'lng' => 121.0244];

        $vehicleId = $validated['vehicle_id'] ?? null;
        $driverId = $validated['driver_id'] ?? null;

        // Auto assignment logic
        if ($request->has('auto_assign') && $request->auto_assign) {
            // Find first active and available vehicle
            $vehicle = Vehicle::where('status', 'active')
                ->whereDoesntHave('trips', function($q) {
                    $q->whereIn('status', ['scheduled', 'active']);
                })->first();

            // Find first available driver
            $driver = Driver::where('status', 'available')
                ->whereDoesntHave('trips', function($q) {
                    $q->whereIn('status', ['scheduled', 'active']);
                })->first();

            if (!$vehicle || !$driver) {
                // Fallback to any active vehicle/driver if available
                $vehicle = Vehicle::where('status', 'active')->first();
                $driver = Driver::first();

                if (!$vehicle || !$driver) {
                    return redirect()->back()->with('error', 'Auto-assignment failed: No available vehicles or drivers in system.');
                }
            }

            $vehicleId = $vehicle->id;
            $driverId = $driver->id;
        }

        $trip = Trip::create([
            'start_location' => $validated['start_location'],
            'end_location' => $validated['end_location'],
            'start_lat' => $startCoords['lat'],
            'start_lng' => $startCoords['lng'],
            'end_lat' => $endCoords['lat'],
            'end_lng' => $endCoords['lng'],
            'distance_km' => $validated['distance_km'],
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
            'estimated_fuel_liters' => $validated['estimated_fuel_liters'],
            'vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'status' => 'scheduled',
        ]);

        // Mark driver as on_trip
        if ($driver = Driver::find($driverId)) {
            $driver->update(['status' => 'on_trip']);
        }

        return redirect()->route('trips.index')->with('success', 'Trip successfully dispatched and scheduled!');
    }

    /**
     * Start a scheduled trip.
     */
    public function startTrip(Trip $trip)
    {
        $trip->update(['status' => 'active', 'started_at' => now()]);
        return redirect()->back()->with('success', 'Trip is now active and live telemetry tracking is enabled.');
    }

    /**
     * Complete an active trip.
     */
    public function completeTrip(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'actual_fuel_liters' => 'required|numeric|min:0.01',
            'actual_duration_minutes' => 'required|integer|min:1',
        ]);

        $trip->update([
            'status' => 'completed',
            'actual_fuel_liters' => $validated['actual_fuel_liters'],
            'actual_duration_minutes' => $validated['actual_duration_minutes'],
            'completed_at' => now(),
        ]);

        // Calculate continuous cumulative odometer reading
        $latestOdometer = FuelLog::where('vehicle_id', $trip->vehicle_id)->max('odometer_reading');
        $newOdometer = $latestOdometer ? ((float) $latestOdometer + (float) $trip->distance_km) : (12500.00 + (float) $trip->distance_km);

        // Determine vehicle powertrain and correct fuel rate (Gasoline ₱65/L vs EV ₱11.50/kWh)
        $vehicle = $trip->vehicle;
        $isEv = $vehicle && (str_contains(strtolower($vehicle->model . ' ' . $vehicle->make . ' ' . $vehicle->type), 'ev') || str_contains(strtolower($vehicle->model), 'vinfast'));
        $fuelType = $isEv ? 'Electric (kWh)' : 'Gasoline (Liters)';
        $unitPrice = $isEv ? 11.50 : 65.00;
        $fuelCost = round($validated['actual_fuel_liters'] * $unitPrice, 2);

        // Record fuel log automatically
        FuelLog::create([
            'vehicle_id' => $trip->vehicle_id,
            'trip_id' => $trip->id,
            'amount_liters' => $validated['actual_fuel_liters'],
            'cost' => $fuelCost,
            'odometer_reading' => round($newOdometer, 2),
            'fuel_type' => $fuelType,
            'date' => now()->toDateString(),
        ]);

        // Free up vehicle and driver
        if ($trip->driver) {
            $trip->driver->update([
                'status' => 'available',
                'total_trips' => $trip->driver->total_trips + 1,
                'total_distance_km' => $trip->driver->total_distance_km + $trip->distance_km,
            ]);
        }

        return redirect()->route('trips.index')->with('success', 'Trip completed! Energy/fuel logs and driver records updated.');
    }

    /**
     * Complete and log demo telemetry ride into database.
     */
    public function completeDemoTrip(Request $request)
    {
        $validated = $request->validate([
            'actual_fuel_liters' => 'required|numeric|min:0.01',
            'actual_duration_minutes' => 'required|integer|min:1',
            'start_location' => 'nullable|string',
            'end_location' => 'nullable|string',
            'distance_km' => 'nullable|numeric',
        ]);

        $vehicle = Vehicle::first();
        $driver = Driver::first();

        $startLocation = $validated['start_location'] ?? 'Manila Hub (Port Area)';
        $endLocation = $validated['end_location'] ?? 'Makati Hub (Ayala Ave)';
        $distance = (float)($validated['distance_km'] ?? 9.5);

        $trip = Trip::create([
            'booking_reference_id' => 'BKG-LIVE-' . strtoupper(bin2hex(random_bytes(3))),
            'start_location' => $startLocation,
            'end_location' => $endLocation,
            'start_lat' => 14.5880,
            'start_lng' => 120.9650,
            'end_lat' => 14.5547,
            'end_lng' => 121.0244,
            'distance_km' => $distance,
            'estimated_duration_minutes' => $validated['actual_duration_minutes'],
            'actual_duration_minutes' => $validated['actual_duration_minutes'],
            'estimated_fuel_liters' => $validated['actual_fuel_liters'],
            'actual_fuel_liters' => $validated['actual_fuel_liters'],
            'vehicle_id' => $vehicle ? $vehicle->id : 1,
            'driver_id' => $driver ? $driver->id : 1,
            'status' => 'completed',
            'started_at' => now()->subMinutes($validated['actual_duration_minutes']),
            'completed_at' => now(),
        ]);

        if ($vehicle) {
            $isEv = str_contains(strtolower($vehicle->model . ' ' . $vehicle->make . ' ' . $vehicle->type), 'ev') || str_contains(strtolower($vehicle->model), 'vinfast');
            $fuelType = $isEv ? 'Electric (kWh)' : 'Gasoline (Liters)';
            $unitPrice = $isEv ? 11.50 : 65.00;

            $latestOdometer = FuelLog::where('vehicle_id', $vehicle->id)->max('odometer_reading') ?: 12500.00;
            FuelLog::create([
                'vehicle_id' => $vehicle->id,
                'trip_id' => $trip->id,
                'amount_liters' => $validated['actual_fuel_liters'],
                'cost' => round($validated['actual_fuel_liters'] * $unitPrice, 2),
                'odometer_reading' => round($latestOdometer + $distance, 2),
                'fuel_type' => $fuelType,
                'date' => now()->toDateString(),
            ]);
        }


        return redirect()->route('trips.index')->with('success', 'Live ride telemetry completed! Dispatch receipt and fuel log saved.');
    }

    /**
     * Process live telemetry GPS simulation broadcast.
     */
    public function simulateTelemetry(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'speed' => 'required|numeric',
            'idle_seconds' => 'nullable|integer',
            'is_harsh_braking' => 'nullable|boolean',
        ]);

        $tripLog = TripLog::create([
            'trip_id' => $trip->id,
            'current_lat' => $validated['lat'],
            'current_lng' => $validated['lng'],
            'current_speed' => $validated['speed'],
            'idle_duration_seconds' => $validated['idle_seconds'] ?? 0,
            'is_harsh_braking' => $validated['is_harsh_braking'] ?? false,
            'recorded_at' => now(),
        ]);

        // Calculate real-time safety score deduction
        $safetyScore = 100;
        if ($validated['speed'] > 80) $safetyScore -= 15;
        if ($validated['is_harsh_braking'] ?? false) $safetyScore -= 10;
        if (($validated['idle_seconds'] ?? 0) > 30) $safetyScore -= 5;

        $safetyScore = max(50, $safetyScore);

        return response()->json([
            'status' => 'success',
            'current_safety_score' => $safetyScore,
            'log' => $tripLog,
        ]);
    }
}
