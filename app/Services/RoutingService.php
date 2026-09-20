<?php

namespace App\Services;

class RoutingService
{
    // Pre-configured hubs with coordinates in Metro Manila (ideal for TNVS capstone)
    private $hubs = [
        'Manila' => ['lat' => 14.5995, 'lng' => 120.9842],
        'Makati' => ['lat' => 14.5547, 'lng' => 121.0244],
        'BGC' => ['lat' => 14.5492, 'lng' => 121.0558],
        'Quezon City' => ['lat' => 14.6760, 'lng' => 121.0437],
        'Pasay' => ['lat' => 14.5378, 'lng' => 120.9993],
        'NAIA' => ['lat' => 14.5204, 'lng' => 121.0134],
        'Alabang' => ['lat' => 14.4172, 'lng' => 121.0408],
        'Ortigas' => ['lat' => 14.5869, 'lng' => 121.0614],
    ];

    public function getHubs(): array
    {
        return $this->hubs;
    }

    /**
     * Compute distance between any two lat/lng coordinates using Haversine formula.
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    /**
     * Helper to resolve hub coordinates matching exact or partial key names.
     */
    public function resolveHubCoords(string $name): array
    {
        foreach ($this->hubs as $key => $coords) {
            if ($name === $key || str_contains(strtolower($name), strtolower($key)) || str_contains(strtolower($key), strtolower($name))) {
                return $coords;
            }
        }
        return ['lat' => 14.5995, 'lng' => 120.9842];
    }

    /**
     * Generate routes/alternatives between start and destination hubs, 
     * simulating traffic and recommending the most fuel-efficient option.
     */
    public function planRoute(string $start, string $end, string $vehicleType, string $fuelType = 'gasoline'): array
    {
        $startCoords = $this->resolveHubCoords($start);
        $endCoords = $this->resolveHubCoords($end);

        $lat1 = $startCoords['lat'];
        $lng1 = $startCoords['lng'];
        $lat2 = $endCoords['lat'];
        $lng2 = $endCoords['lng'];

        $distance = $this->haversineDistance($lat1, $lng1, $lat2, $lng2);
        $fuelPredictor = new FuelPredictionService();

        // Rate per unit and label based on fuel type
        $ratePerUnit = match (strtolower($fuelType)) {
            'gasoline', 'gas', 'unleaded' => 64.50, // ₱64.50 per Liter Gas
            'diesel' => 58.00, // ₱58.00 per Liter Diesel
            'electric', 'ev' => 11.50, // ₱11.50 per kWh EV
            default => 64.50,
        };

        $fuelUnit = match (strtolower($fuelType)) {
            'gasoline', 'gas', 'unleaded' => 'Liters (Gas)',
            'diesel' => 'Liters (Diesel)',
            'electric', 'ev' => 'kWh (EV)',
            default => 'Liters (Gas)',
        };

        // 1. Direct Eco Route
        $speed1 = 48.5;
        $kwh1 = $fuelPredictor->predict($distance, $speed1, $vehicleType, $fuelType);
        $cost1 = round($kwh1 * $ratePerUnit, 2);
        $duration1 = round(($distance / $speed1) * 60);

        // 2. Highway / Express Route
        $dist2 = round($distance * 1.15, 1);
        $speed2 = 68.0;
        $kwh2 = $fuelPredictor->predict($dist2, $speed2, $vehicleType, $fuelType);
        $cost2 = round($kwh2 * $ratePerUnit, 2);
        $duration2 = round(($dist2 / $speed2) * 60);

        // 3. City Bypass Route
        $dist3 = round($distance * 1.25, 1);
        $speed3 = 35.0;
        $kwh3 = $fuelPredictor->predict($dist3, $speed3, $vehicleType, $fuelType);
        $cost3 = round($kwh3 * $ratePerUnit, 2);
        $duration3 = round(($dist3 / $speed3) * 60);

        $routesList = [
            [
                'name' => 'Eco-Optimized Route (Recommended)',
                'tag' => 'Recommended Eco-Path 🌿',
                'distance_km' => $distance,
                'avg_speed_kmh' => $speed1,
                'duration_minutes' => $duration1,
                'traffic_condition' => '🟢 Low Congestion (Flowing @ 48 km/h)',
                'predicted_kwh' => $kwh1,
                'estimated_fuel' => $kwh1,
                'fuel_unit' => $fuelUnit,
                'fuel_type' => ucfirst($fuelType),
                'charging_cost_php' => number_format($cost1, 2),
                'description' => "Optimized for $vehicleType ($fuelUnit). Bypasses heavy intersections.",
                'is_eco' => true,
                'color' => '#10B981',
                'path' => $this->generateInterpolatedPath($startCoords, $endCoords, 0.05)
            ],
            [
                'name' => 'Expressway / Skyway Route',
                'tag' => 'Fastest ETA ⚡',
                'distance_km' => $dist2,
                'avg_speed_kmh' => $speed2,
                'duration_minutes' => $duration2,
                'traffic_condition' => '🟡 Moderate Highway Flow (Speed: 68 km/h)',
                'predicted_kwh' => $kwh2,
                'estimated_fuel' => $kwh2,
                'fuel_unit' => $fuelUnit,
                'fuel_type' => ucfirst($fuelType),
                'charging_cost_php' => number_format($cost2, 2),
                'description' => 'Higher average speed via Skyway corridor. Saves up to 8 minutes travel time.',
                'is_eco' => false,
                'color' => '#3B82F6',
                'path' => $this->generateInterpolatedPath($startCoords, $endCoords, -0.06)
            ],
            [
                'name' => 'Standard City Arterial Route',
                'tag' => 'City Bypass 🚗',
                'distance_km' => $dist3,
                'avg_speed_kmh' => $speed3,
                'duration_minutes' => $duration3,
                'traffic_condition' => '🔴 Heavy Urban Traffic (+12 min delay)',
                'predicted_kwh' => $kwh3,
                'estimated_fuel' => $kwh3,
                'fuel_unit' => $fuelUnit,
                'fuel_type' => ucfirst($fuelType),
                'charging_cost_php' => number_format($cost3, 2),
                'description' => 'Follows main surface avenues (Taft/EDSA). High stop-and-go fuel consumption.',
                'is_eco' => false,
                'color' => '#F59E0B',
                'path' => $this->generateInterpolatedPath($startCoords, $endCoords, 0.01)
            ]
        ];

        return [
            'start' => $start,
            'end' => $end,
            'start_coords' => $startCoords,
            'end_coords' => $endCoords,
            'routes' => $routesList
        ];
    }

    private function getCongestionText(float $congestion): string
    {
        if ($congestion > 1.8) return 'Heavy';
        if ($congestion > 1.3) return 'Moderate';
        return 'Clear';
    }

    /**
     * Interpolates intermediate coordinates using quadratic Bezier curves for smooth OpenStreetMap polyline rendering.
     */
    private function generateInterpolatedPath(array $start, array $end, float $curveOffset = 0.0): array
    {
        $points = [];
        $steps = 12;
        $dLat = $end['lat'] - $start['lat'];
        $dLng = $end['lng'] - $start['lng'];
        $perpLat = -$dLng * $curveOffset;
        $perpLng = $dLat * $curveOffset;

        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $curveFactor = 4 * $t * (1 - $t);
            $lat = $start['lat'] + $t * $dLat + $perpLat * $curveFactor;
            $lng = $start['lng'] + $t * $dLng + $perpLng * $curveFactor;

            $points[] = [
                'lat' => round($lat, 6),
                'lng' => round($lng, 6),
            ];
        }
        return $points;
    }
}
