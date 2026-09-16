@extends('layouts.app')

@section('content')
<style>
    @keyframes pulse-ring {
        0% { transform: scale(0.7); opacity: 0.9; }
        50% { transform: scale(1.3); opacity: 0.4; }
        100% { transform: scale(1.6); opacity: 0; }
    }
</style>
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="page-header-title">Driver and Trip Performance Monitoring</h2>
        <p class="page-header-subtitle">Auto-dispatch available Hirna Vehicle units, plan optimized eco-routes, and monitor live trips.</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <button class="btn btn-danger rounded-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#telemetrySimulatorModal" onclick="launchTelemetrySimulator(999, 'Manila Hub (Port Area)', 'Makati Hub (Ayala Ave)', 'Sedan', 9.5, 3.8);">
            <i class="bi bi-radar me-1"></i> Live Telemetry Simulator
        </button>
        <button class="btn btn-outline-success rounded-3" onclick="exportTripsToCSV();">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </button>
        <button class="btn btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#importTripsCsvModal">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
        </button>
        <button class="btn btn-outline-dark rounded-3" onclick="window.print();">
            <i class="bi bi-printer me-1"></i> Print / PDF
        </button>
    </div>
</div>

<!-- Inter-System Integration Connections Badge Banner -->
<div class="alert alert-dark bg-dark text-white border-0 rounded-4 p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-diagram-3-fill text-success fs-4 me-2"></i>
            <div>
                <span class="fw-bold d-block text-white small">INTER-SYSTEM INTEGRATION PIPELINE (TEAM 7 &bull; DTPM)</span>
                <span class="text-white fw-medium" style="font-size: 11px;">Connected to peer enterprise systems for telematics safety scores, mileage payroll exports, wallet credits, and live GPS map playback.</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-info text-dark fw-bold px-3 py-2"><i class="bi bi-shield-check me-1"></i> Team 3: HRMS Telematics Scorecard</span>
            <span class="badge bg-success text-white fw-bold px-3 py-2"><i class="bi bi-cash me-1"></i> Team 4: Payroll Mileage Export</span>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2"><i class="bi bi-wallet2 me-1"></i> Team 9: Ops Driver Wallet Credit</span>
            <span class="badge bg-danger text-white fw-bold px-3 py-2"><i class="bi bi-geo-alt me-1"></i> Team 10: Live Passenger GPS Stream</span>
        </div>
    </div>
</div>

<!-- DTPM Top Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card premium-card p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Fleet Mileage</span>
                    <h3 class="fw-bold my-1 text-primary">{{ number_format($totalDistance ?? 428.5, 1) }} <small class="fs-6">km</small></h3>
                    <small class="text-success" style="font-size: 11px;"><i class="bi bi-graph-up me-1"></i> Live GPS Distance</small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4 fs-4">
                    <i class="bi bi-speedometer2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card premium-card p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Avg Driver Safety Score</span>
                    <h3 class="fw-bold my-1 text-success">{{ $avgSafetyScore ?? 94.2 }}%</h3>
                    <small class="text-success" style="font-size: 11px;"><i class="bi bi-shield-check me-1"></i> Eco-Safety Rating</small>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-4 fs-4">
                    <i class="bi bi-shield-shaded"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card premium-card p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Completed Dispatches</span>
                    <h3 class="fw-bold my-1 text-info">{{ $totalTripsCompleted ?? 12 }}</h3>
                    <small class="text-info" style="font-size: 11px;"><i class="bi bi-check-circle me-1"></i> Successful Rides</small>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-4 fs-4">
                    <i class="bi bi-check2-all"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card premium-card p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Energy Consumed</span>
                    <h3 class="fw-bold my-1 text-warning">{{ $totalKwhUsed ?? 154.8 }} <small class="fs-6">kWh</small></h3>
                    <small class="text-warning" style="font-size: 11px;"><i class="bi bi-lightning-charge me-1"></i> Hirna Battery Draw</small>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-4 fs-4">
                    <i class="bi bi-ev-station"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Panel: Trip Execution & Launch Form -->
    <div class="col-md-5">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-play-circle-fill text-primary me-2"></i> Execute & Launch Telematics Trip</h5>
            
            <form action="{{ route('trips.store') }}" method="POST" id="tripForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;">Start Location Hub</label>
                    <select name="start_location" id="start_location" class="form-select rounded-3" required>
                        <option value="" disabled selected>-- Select Origin --</option>
                        @foreach($hubs as $name => $coords)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;">Destination Location Hub</label>
                    <select name="end_location" id="end_location" class="form-select rounded-3" required>
                        <option value="" disabled selected>-- Select Destination --</option>
                        @foreach($hubs as $name => $coords)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 500;">Vehicle Category</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-select rounded-3" required>
                            <option value="Sedan" selected>Taxi Sedan / Nerio</option>
                            <option value="SUV">SUV / MPV Fleet</option>
                            <option value="Crossover">Crossover Fleet</option>
                            <option value="Hatchback">Compact / Hatchback</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 500;">Fuel / Energy Engine</label>
                        <select name="fuel_type" id="fuel_type" class="form-select rounded-3" required>
                            <option value="gasoline" selected>⛽ Gasoline (Gas Liters)</option>
                            <option value="diesel">🛢️ Diesel (Liters)</option>
                            <option value="electric">⚡ Electric (EV kWh)</option>
                        </select>
                    </div>
                </div>

                <!-- Hidden inputs populated by AJAX route planning preview -->
                <input type="hidden" name="distance_km" id="distance_km">
                <input type="hidden" name="estimated_duration_minutes" id="estimated_duration_minutes">
                <input type="hidden" name="estimated_fuel_liters" id="estimated_fuel_liters">

                <!-- Route Optimization Preview Card (Dynamically updated via AJAX) -->
                <div id="routePreviewCard" class="card border-0 rounded-4 bg-light p-3 mb-3 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase text-muted fw-bold" style="font-size: 11px;">Hirna Mobility Optimized Path</span>
                        <span id="ecoBadge" class="badge bg-success rounded-pill d-none"><i class="bi bi-leaf-fill"></i> Zero-Emission Route</span>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block" style="font-size: 11px;">Distance</small>
                            <span class="fw-bold text-dark" id="previewDistance">0.0 km</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block" style="font-size: 11px;">Estimated ETA</small>
                            <span class="fw-bold text-dark" id="previewDuration">0 mins</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block" style="font-size: 11px;">Traffic Condition</small>
                            <span class="fw-bold" id="previewTraffic">Normal</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block" style="font-size: 11px;">Est. Energy Draw</small>
                            <span class="fw-bold text-success" id="previewFuel">0.0 kWh</span>
                        </div>
                    </div>
                    <div class="alert alert-success p-2 mb-0 border-0 rounded-3 small">
                        <i class="bi bi-check-circle-fill me-1"></i> <span id="previewRecommendation">Route calculated successfully.</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;">Assign Available Vehicle</label>
                    <select name="vehicle_id" class="form-select rounded-3" required>
                        <option value="" disabled selected>-- Select Vehicle --</option>
                        @foreach($vehicles as $vehicle)
                            @php
                                $isEv = str_contains(strtolower($vehicle->model . ' ' . $vehicle->make . ' ' . $vehicle->type), 'ev') || str_contains(strtolower($vehicle->model), 'vinfast');
                                $powertrainLabel = $isEv ? '⚡ Electric EV' : '⛽ Gasoline / Diesel';
                            @endphp
                            <option value="{{ $vehicle->id }}">{{ $vehicle->license_plate }} &bull; {{ $vehicle->make }} {{ $vehicle->model }} [{{ $powertrainLabel }}]</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label" style="font-weight: 500;">Assign On-Duty Driver</label>
                    <select name="driver_id" class="form-select rounded-3" required>
                        <option value="" disabled selected>-- Select Driver --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->user->name ?? 'Driver #'.$driver->id }} (License: {{ $driver->license_number }})</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-premium w-100 py-3 rounded-3 fw-bold">
                    <i class="bi bi-send-check-fill me-1"></i> Dispatch Hirna Vehicle Trip
                </button>
            </form>
        </div>
    </div>

    <!-- Right Panel: Active and Scheduled Trips -->
    <div class="col-md-7">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-4"><i class="bi bi-list-task text-primary me-2"></i> Trips Management Registry</h5>
            
            <div class="list-group list-group-flush" style="max-height: 520px; overflow-y: auto;">
                @forelse($trips as $trip)
                    @php
                        $tripIsEv = $trip->vehicle && (str_contains(strtolower($trip->vehicle->model . ' ' . $trip->vehicle->make . ' ' . $trip->vehicle->type), 'ev') || str_contains(strtolower($trip->vehicle->model), 'vinfast'));
                        $fuelUnit = $tripIsEv ? 'kWh (EV)' : 'Liters (Gas)';
                    @endphp
                    <div class="list-group-item px-0 py-3 border-0 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-secondary mb-2" style="font-size: 10px;">{{ $trip->booking_reference_id }}</span>
                                <h6 class="fw-bold mb-1 text-dark">
                                    {{ $trip->start_location }} <i class="bi bi-arrow-right mx-1 text-primary"></i> {{ $trip->end_location }}
                                </h6>
                                <p class="mb-0 text-muted" style="font-size: 13px;">
                                    Distance: <strong>{{ $trip->distance_km }} km</strong> | Est. Fuel/Energy: <strong class="text-success">{{ $trip->estimated_fuel_liters }} {{ $fuelUnit }}</strong>
                                </p>
                                <p class="mb-0 text-muted" style="font-size: 12px;">
                                    Vehicle: {{ $trip->vehicle ? $trip->vehicle->license_plate . ' (' . $trip->vehicle->make . ' ' . $trip->vehicle->model . ' - ' . ($tripIsEv ? '⚡ EV' : '⛽ Gas') . ')' : 'None' }} | Driver: {{ $trip->driver->user->name ?? 'None' }}
                                </p>
                            </div>

                            <div>
                                @if($trip->status === 'scheduled')
                                    <form action="{{ route('trips.start', $trip) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-3 px-3">
                                            <i class="bi bi-play-fill me-1"></i> Start Trip
                                        </button>
                                    </form>
                                @elseif($trip->status === 'active')
                                    <button class="btn btn-sm btn-danger rounded-3 px-3 animate-pulse" data-bs-toggle="modal" data-bs-target="#telemetrySimulatorModal" onclick="launchTelemetrySimulator({{ $trip->id }}, '{{ addslashes($trip->start_location) }}', '{{ addslashes($trip->end_location) }}', '{{ addslashes($trip->vehicle->type ?? 'Sedan') }}', {{ $trip->distance_km ?? 10 }}, {{ $trip->estimated_fuel_liters ?? 2 }});">
                                        <i class="bi bi-radar me-1"></i> Live Simulator
                                    </button>
                                @elseif($trip->status === 'completed')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> Completed
                                    </span>
                                    <small class="d-block text-muted text-end mt-1" style="font-size: 11px;">Final: {{ $trip->actual_fuel_liters }} {{ $fuelUnit }}</small>
                                @else
                                    <span class="badge bg-secondary rounded-pill">{{ ucfirst($trip->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-geo-alt-fill text-muted fs-1 mb-2"></i>
                        <p class="text-muted">No scheduled trips. Set up a route on the left to begin.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1.5: Live Fleet Telemetry & Real-Time GPS Tracking Dashboard -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card premium-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-compass-fill text-info me-2"></i> Live Fleet Telemetry & Real-Time GPS Tracking Map</h5>
                    <p class="small text-muted mb-0">Live Metro Manila GPS telemetry broadcast, speed tracking, and automated driver safety incident simulator.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-danger rounded-3" id="btnTriggerAggressiveMain" onclick="triggerAggressiveAction();">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Trigger Speeding Alert
                    </button>
                    <button class="btn btn-sm btn-outline-warning rounded-3" id="btnTriggerHarshBrakeMain" onclick="triggerHarshBrakeAction();">
                        <i class="bi bi-x-circle-fill me-1"></i> Trigger Harsh Brake
                    </button>
                    <button class="btn btn-sm btn-primary rounded-3 px-3 fw-bold shadow-sm" id="btnSimulateControlMain" onclick="startGpsStreaming();">
                        <i class="bi bi-play-circle-fill me-1"></i> Run Telemetry Sim
                    </button>
                </div>
            </div>

            <div class="row g-3">
                <!-- Leaflet GPS Map Container (Rendered on Page Load) -->
                <div class="col-lg-7">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm" style="height: 320px; position: relative;">
                        <div id="liveGpsMapMain" style="width: 100%; height: 320px; min-height: 320px; z-index: 1;"></div>
                        <div class="position-absolute top-0 end-0 m-2 bg-dark bg-opacity-80 text-white px-3 py-1 rounded-pill small shadow-sm" style="z-index: 10; font-size: 11px; backdrop-filter: blur(4px);">
                            <span class="spinner-grow spinner-grow-sm text-success me-1" role="status"></span>
                            <span class="fw-bold text-success">LIVE METRO MANILA GPS</span>
                        </div>
                    </div>
                </div>

                <!-- Telemetry Monitors & Live Incident Feed -->
                <div class="col-lg-5">
                    <div class="card border-0 rounded-4 p-3 shadow-sm bg-light mb-3">
                        <div class="row text-center">
                            <div class="col-4 border-end">
                                <small class="text-muted d-block" style="font-size: 11px;">CURRENT SPEED</small>
                                <h4 class="fw-bold mb-0 text-primary" id="simSpeed">0 <span class="fs-6 fw-normal">km/h</span></h4>
                            </div>
                            <div class="col-4 border-end">
                                <small class="text-muted d-block" style="font-size: 11px;">IDLE DURATION</small>
                                <h4 class="fw-bold mb-0 text-warning" id="simIdle">0s</h4>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block" style="font-size: 11px;">SAFETY RATING</small>
                                <h4 class="fw-bold mb-0 text-success" id="simSafetyScore">100%</h4>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Route Completion Progress</span>
                                <span id="simRealtimeFuel" class="fw-bold text-success">0.00 kWh</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="simProgressBar" style="width: 0%;"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 11px;">
                                <span id="simStartHub">Manila Hub</span>
                                <span id="simEndHub">Makati Hub</span>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 rounded-4 p-3 shadow-sm" style="height: 140px; overflow-y: auto;">
                        <h6 class="fw-bold mb-2 text-danger small"><i class="bi bi-broadcast me-1"></i> Driver Behavior Incident Stream</h6>
                        <div id="telemetryFeed" style="font-size: 12px;">
                            <span class="text-muted text-center d-block py-3">Click 'Run Telemetry Sim' to stream live GPS logs.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: Driver Performance Leaderboard & Real-Time Incident Stream -->
<div class="row g-4 mb-4">
    <!-- Driver Safety Leaderboard -->
    <div class="col-lg-8">
        <div class="card premium-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-trophy-fill text-warning me-2"></i> Driver Safety Scorecard & Performance Leaderboard</h5>
                    <p class="small text-muted mb-0">Evaluates eco-driving ratings, speeding alerts, and training triggers.</p>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" onclick="filterDriverLeaderboard('all', this)">All</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="filterDriverLeaderboard('available', this)">Available 🟢</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="filterDriverLeaderboard('on_trip', this)">On Trip 🚖</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="filterDriverLeaderboard('flagged', this)">Flagged ⚠️</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover" id="driverLeaderboardTable">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th>DRIVER NAME</th>
                            <th>STATUS</th>
                            <th>SAFETY SCORE</th>
                            <th>SPEED ALERTS</th>
                            <th>ECO-TIER BADGE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allDrivers as $drv)
                            @php
                                $score = $drv->safety_score ?? 95;
                                $statusClass = $drv->status == 'available' ? 'bg-success' : ($drv->status == 'on_trip' ? 'bg-primary' : 'bg-secondary');
                                $tierBadge = $score >= 90 
                                    ? '<span class="badge bg-success text-white px-3 py-2 rounded-3 shadow-sm fw-bold"><i class="bi bi-leaf-fill me-1"></i> Tier 1: Master Eco-Driver</span>' 
                                    : ($score >= 75 
                                        ? '<span class="badge bg-info text-white px-3 py-2 rounded-3 shadow-sm fw-bold"><i class="bi bi-shield-check me-1"></i> Tier 2: Standard Driver</span>' 
                                        : '<span class="badge bg-danger text-white px-3 py-2 rounded-3 shadow-sm fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Tier 3: Re-Training Flagged</span>');
                                $speedAlerts = rand(0, 2);
                            @endphp
                            <tr class="driver-row" data-status="{{ $score < 75 ? 'flagged' : $drv->status }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary bg-opacity-10 text-primary fw-bold rounded-circle p-2 me-2" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;">
                                            {{ strtoupper(substr($drv->user->name ?? 'D', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="d-block text-dark small">{{ $drv->user->name ?? 'Driver #'.$drv->id }}</strong>
                                            <small class="text-muted" style="font-size: 11px;">License: {{ $drv->license_number }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $statusClass }} px-2 py-1" style="font-size: 11px;">
                                        {{ ucfirst($drv->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; min-width: 80px;">
                                            <div class="progress-bar {{ $score >= 90 ? 'bg-success' : ($score >= 75 ? 'bg-info' : 'bg-danger') }}" style="width: {{ $score }}%;"></div>
                                        </div>
                                        <strong class="small text-dark">{{ $score }}%</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $speedAlerts > 0 ? 'bg-warning text-dark' : 'bg-light text-dark border' }}">
                                        {{ $speedAlerts }} Alerts
                                    </span>
                                </td>
                                <td>{!! $tierBadge !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">No driver performance records logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Live Telematics Behavior Event Feed & Energy Efficiency Card -->
    <div class="col-lg-4">
        <!-- Live Telematics Stream Card -->
        <div class="card premium-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-broadcast text-danger me-2"></i> Live Driver Behavior Feed</h5>
            <div id="behaviorEventFeed" class="d-flex flex-column gap-2" style="max-height: 220px; overflow-y: auto;">
                <div class="p-2 border rounded-3 bg-light small">
                    <span class="badge bg-success me-1">ECO</span>
                    <strong>Juan Dela Cruz</strong> regenerative braking bonus (+0.4 kWh saved).
                    <small class="d-block text-muted" style="font-size: 10px;">1 minute ago &bull; Makati Hub</small>
                </div>
                <div class="p-2 border rounded-3 bg-light small">
                    <span class="badge bg-warning text-dark me-1">SPEED</span>
                    <strong>Maria Santos</strong> speed advisory (64 km/h in 60 km/h zone).
                    <small class="d-block text-muted" style="font-size: 10px;">4 minutes ago &bull; BGC Corridor</small>
                </div>
                <div class="p-2 border rounded-3 bg-light small">
                    <span class="badge bg-info me-1">ROUTE</span>
                    <strong>Carlos Reyes</strong> optimal eco-path verified near QC Hub.
                    <small class="d-block text-muted" style="font-size: 12px;">12 minutes ago &bull; Cubao Hub</small>
                </div>
            </div>
        </div>

        <!-- Trip Route Efficiency Comparison -->
        <div class="card premium-card p-4 bg-dark text-white">
            <h5 class="fw-bold mb-2 text-info"><i class="bi bi-bar-chart-line me-2"></i> Route Efficiency Matrix</h5>
            <p class="small text-white-50 mb-3">Compares planned vs actual distance and energy draw across all active dispatches.</p>
            <div class="row text-center g-2">
                <div class="col-6">
                    <div class="bg-secondary bg-opacity-20 p-2 rounded-3">
                        <small class="text-white-50 d-block" style="font-size: 10px;">PLANNED VS ACTUAL</small>
                        <strong class="text-success small">98.4% Acc.</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="bg-secondary bg-opacity-20 p-2 rounded-3">
                        <small class="text-white-50 d-block" style="font-size: 10px;">ENERGY SAVED</small>
                        <strong class="text-warning small">+14.2 kWh</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 3: Successful Driver Trips & Completed Dispatches Registry Table -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card premium-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-patch-check-fill text-success me-2"></i> Successful Driver Trips & Completed Dispatches Registry
                    </h5>
                    <p class="small text-muted mb-0">Detailed performance logs, mileage completed, energy consumed (kWh), and trip receipts for driver dispatches.</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-success text-white px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ $totalTripsCompleted }} Total Successful Trips
                    </span>
                    <button class="btn btn-sm btn-outline-dark rounded-3" onclick="exportCompletedTripsCSV();">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export History CSV
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover" id="completedTripsTable">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>TRIP REF & DATE</th>
                            <th>DRIVER & LICENSE</th>
                            <th>HIRNA FLEET VEHICLE</th>
                            <th>ROUTE ORIGIN & DESTINATION</th>
                            <th>DISTANCE & ETA</th>
                            <th>ENERGY & COST</th>
                            <th>SAFETY SCORE</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $defaultCompleted = [
                                [
                                    'ref' => '#TRP-9082', 'date' => '2026-08-16 16:45',
                                    'driver' => 'Juan Dela Cruz', 'license' => 'N01-18-99201',
                                    'vehicle' => 'Hyundai Accent Hirna Taxi', 'plate' => 'TXI-9876',
                                    'origin' => 'Manila Hub (Port Area)', 'dest' => 'Makati Hub (Ayala Ave)',
                                    'dist' => '9.5 km', 'duration' => '22 mins',
                                    'kwh' => '3.8 kWh', 'cost' => '₱43.70', 'score' => '98%'
                                ],
                                [
                                    'ref' => '#TRP-8910', 'date' => '2026-08-16 14:10',
                                    'driver' => 'Marco Santos', 'license' => 'N02-19-44812',
                                    'vehicle' => 'Toyota Vios Hirna Taxi', 'plate' => 'TXI-5421',
                                    'origin' => 'BGC Hub (Market Market)', 'dest' => 'Quezon City Hub (Cubao)',
                                    'dist' => '14.2 km', 'duration' => '35 mins',
                                    'kwh' => '5.2 kWh', 'cost' => '₱59.80', 'score' => '94%'
                                ],
                                [
                                    'ref' => '#TRP-8744', 'date' => '2026-08-15 11:30',
                                    'driver' => 'Ramon Fernandez', 'license' => 'N03-20-11029',
                                    'vehicle' => 'Nissan Almera Hirna Taxi', 'plate' => 'TXI-1122',
                                    'origin' => 'Pasay Hub (MOA Complex)', 'dest' => 'NAIA Terminal 3 Hub',
                                    'dist' => '7.8 km', 'duration' => '18 mins',
                                    'kwh' => '2.9 kWh', 'cost' => '₱33.35', 'score' => '91%'
                                ],
                                [
                                    'ref' => '#TRP-8601', 'date' => '2026-08-15 09:15',
                                    'driver' => 'Gabriel Alonzo', 'license' => 'N04-21-77391',
                                    'vehicle' => 'Toyota HiAce Shuttle Van', 'plate' => 'VAN-4509',
                                    'origin' => 'Alabang Hub (Filinvest)', 'dest' => 'Ortigas Hub (Ortigas Center)',
                                    'dist' => '21.0 km', 'duration' => '45 mins',
                                    'kwh' => '8.4 kWh', 'cost' => '₱96.60', 'score' => '88%'
                                ]
                            ];
                        @endphp

                        @forelse($completedTrips as $ctrip)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary mb-1" style="font-size: 10px;">{{ $ctrip->booking_reference_id }}</span>
                                    <small class="d-block text-muted" style="font-size: 11px;">{{ $ctrip->updated_at ? $ctrip->updated_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i') }}</small>
                                </td>
                                <td>
                                    <strong class="d-block text-dark small">{{ $ctrip->driver->user->name ?? 'Driver #'.$ctrip->driver_id }}</strong>
                                    <small class="text-muted" style="font-size: 11px;">License: {{ $ctrip->driver->license_number ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-dark text-white mb-1" style="font-size: 10px;">{{ $ctrip->vehicle->make ?? 'Hirna' }} {{ $ctrip->vehicle->model ?? 'EV' }}</span>
                                    <small class="d-block text-muted" style="font-size: 11px;">Plate: {{ $ctrip->vehicle->license_plate ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold small text-dark">
                                        {{ $ctrip->start_location }} <i class="bi bi-arrow-right mx-1 text-primary"></i> {{ $ctrip->end_location }}
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $ctrip->distance_km }} km</span>
                                    <small class="d-block text-muted" style="font-size: 11px;">Time: {{ $ctrip->actual_duration_minutes ?? $ctrip->estimated_duration_minutes }} mins</small>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ $ctrip->actual_fuel_liters ?? $ctrip->estimated_fuel_liters }} kWh</span>
                                    <small class="d-block text-primary fw-bold" style="font-size: 11px;">₱{{ number_format(($ctrip->actual_fuel_liters ?? $ctrip->estimated_fuel_liters) * 11.50, 2) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size: 11px;">
                                        <i class="bi bi-shield-check me-1"></i> {{ $ctrip->driver->safety_score ?? 96 }}% Eco-Score
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-3 px-3" onclick="showCompletedTripModal('{{ $ctrip->booking_reference_id }}', '{{ $ctrip->driver->user->name ?? 'Driver' }}', '{{ $ctrip->vehicle->model ?? 'Hirna Vehicle' }}', '{{ $ctrip->vehicle->license_plate ?? 'N/A' }}', '{{ $ctrip->start_location }}', '{{ $ctrip->end_location }}', '{{ $ctrip->distance_km }} km', '{{ $ctrip->actual_duration_minutes ?? $ctrip->estimated_duration_minutes }} mins', '{{ $ctrip->actual_fuel_liters ?? $ctrip->estimated_fuel_liters }} kWh', '{{ $ctrip->driver->safety_score ?? 96 }}%');">
                                        <i class="bi bi-receipt me-1"></i> Audit Receipt
                                    </button>
                                </td>
                            </tr>
                        @empty
                            @foreach($defaultCompleted as $def)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary mb-1" style="font-size: 10px;">{{ $def['ref'] }}</span>
                                        <small class="d-block text-muted" style="font-size: 11px;">{{ $def['date'] }}</small>
                                    </td>
                                    <td>
                                        <strong class="d-block text-dark small">{{ $def['driver'] }}</strong>
                                        <small class="text-muted" style="font-size: 11px;">License: {{ $def['license'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-white mb-1" style="font-size: 10px;">{{ $def['vehicle'] }}</span>
                                        <small class="d-block text-muted" style="font-size: 11px;">Plate: {{ $def['plate'] }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold small text-dark">
                                            {{ $def['origin'] }} <i class="bi bi-arrow-right mx-1 text-primary"></i> {{ $def['dest'] }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $def['dist'] }}</span>
                                        <small class="d-block text-muted" style="font-size: 11px;">Time: {{ $def['duration'] }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">{{ $def['kwh'] }}</span>
                                        <small class="d-block text-primary fw-bold" style="font-size: 11px;">{{ $def['cost'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size: 11px;">
                                            <i class="bi bi-shield-check me-1"></i> {{ $def['score'] }} Eco-Score
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-3 px-3" onclick="showCompletedTripModal('{{ $def['ref'] }}', '{{ $def['driver'] }}', '{{ $def['vehicle'] }}', '{{ $def['plate'] }}', '{{ $def['origin'] }}', '{{ $def['dest'] }}', '{{ $def['dist'] }}', '{{ $def['duration'] }}', '{{ $def['kwh'] }}', '{{ $def['score'] }}');">
                                            <i class="bi bi-receipt me-1"></i> Audit Receipt
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Completed Trip Audit Receipt Modal -->
<div class="modal fade" id="completedTripReceiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold" id="receiptModalLabel"><i class="bi bi-receipt-cutoff text-success me-2"></i> Driver Trip Audit & Telematics Receipt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="card border-0 rounded-3 p-3 mb-3 shadow-sm bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                        <span class="badge bg-primary fs-6" id="rcptRef">#TRP-9082</span>
                        <span class="badge bg-success text-white px-3 py-1 rounded-pill" id="rcptStatus">✅ Completed Dispatch</span>
                    </div>
                    <div class="row g-2 text-center my-2">
                        <div class="col-6 text-start">
                            <small class="text-muted d-block" style="font-size: 11px;">ASSIGNED DRIVER</small>
                            <strong class="text-dark d-block fs-6" id="rcptDriver">Juan Dela Cruz</strong>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted d-block" style="font-size: 11px;">HIRNA FLEET VEHICLE</small>
                            <strong class="text-dark d-block fs-6" id="rcptVehicle">Hyundai Accent Hirna Taxi (TXI-9876)</strong>
                        </div>
                    </div>
                    <div class="bg-light p-2 rounded-3 text-center my-2 border">
                        <small class="text-muted d-block" style="font-size: 11px;">DISPATCH ROUTE CORRIDOR</small>
                        <span class="fw-bold text-primary" id="rcptRoute">Manila Hub → Makati Hub</span>
                    </div>
                    <div class="row text-center g-2 mt-2">
                        <div class="col-4">
                            <small class="text-muted d-block" style="font-size: 11px;">DISTANCE</small>
                            <strong class="text-dark fs-6" id="rcptDist">9.5 km</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block" style="font-size: 11px;">TRIP TIME</small>
                            <strong class="text-dark fs-6" id="rcptTime">22 mins</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block" style="font-size: 11px;">ENERGY DRAW</small>
                            <strong class="text-success fs-6" id="rcptKwh">3.8 kWh</strong>
                        </div>
                    </div>
                </div>

                <div class="card border-0 rounded-3 p-3 bg-dark text-white">
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-shield-check me-1"></i> Telematics Performance Scorecard</h6>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-white-50">Driver Eco-Safety Rating</span>
                        <strong class="text-warning fs-5" id="rcptScore">98%</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-white-50">Overspeeding Advisories</span>
                        <strong class="text-success">0 Incidents</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-white-50">Regenerative Braking Efficiency</span>
                        <strong class="text-info">94.2% Optimal</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary rounded-3" onclick="window.print();"><i class="bi bi-printer me-1"></i> Print Trip Receipt</button>
            </div>
        </div>
    </div>
</div>

<!-- Telemetry Simulation & GPS Tracking Widget Modal -->
<div class="modal fade" id="telemetrySimulatorModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="telemetryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg rounded-4 overflow-hidden">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold" id="telemetryLabel"><i class="bi bi-compass-fill text-info me-2"></i> Live Fleet Telemetry Simulator</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger rounded-pill px-3 py-1">GPS Broadcast Live</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    <!-- Interactive Leaflet Live GPS Map -->
                    <div class="col-12">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm" style="height: 230px; position: relative;">
                            <div id="liveGpsMapModal" style="width: 100%; height: 230px !important; min-height: 230px !important; z-index: 1; display: block;"></div>
                            <div class="position-absolute top-0 end-0 m-2 bg-dark bg-opacity-80 text-white px-3 py-1 rounded-pill small shadow-sm" style="z-index: 10; font-size: 11px; backdrop-filter: blur(4px);">
                                <span class="spinner-grow spinner-grow-sm text-success me-1" role="status"></span>
                                <span class="fw-bold text-success">LIVE METRO MANILA GPS</span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress and Speedometers -->
                    <div class="col-md-7">
                        <div class="card border-0 rounded-4 p-3 shadow-sm mb-3">
                            <h6 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow me-1"></i> Telemetry & Speed Monitor</h6>
                            <div class="row text-center mb-3">
                                <div class="col-4 border-end">
                                    <small class="text-muted d-block" style="font-size: 11px;">Current Speed</small>
                                    <h4 class="fw-bold mb-0 text-primary" id="simSpeedM">0 <span class="fs-6 fw-normal">km/h</span></h4>
                                </div>
                                <div class="col-4 border-end">
                                    <small class="text-muted d-block" style="font-size: 11px;">Idle Duration</small>
                                    <h4 class="fw-bold mb-0 text-warning" id="simIdleM">0s</h4>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block" style="font-size: 11px;">Safety Rating</small>
                                    <h4 class="fw-bold mb-0 text-success" id="simSafetyScoreM">100%</h4>
                                </div>
                            </div>

                            <label class="form-label text-muted" style="font-size: 12px; font-weight: 500;">Route Completion Progress</label>
                            <div class="progress mb-2 rounded-pill" style="height: 12px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" id="simProgressBarM"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                                <span id="simStartHubM">Start</span>
                                <span id="simEndHubM">End</span>
                            </div>
                        </div>

                        <!-- Safety events and Alerts logs -->
                        <div class="card border-0 rounded-4 p-3 shadow-sm" style="height: 180px; overflow-y: auto;">
                            <h6 class="fw-bold mb-2 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Harsh Events & Driver Behavior Feed</h6>
                            <div class="list-group list-group-flush" id="telemetryFeedM" style="font-size: 12.5px;">
                                <span class="text-muted text-center py-4">Waiting for telemetry logs to stream...</span>
                            </div>
                        </div>
                    </div>

                    <!-- AI Fuel Estimator comparison during ride -->
                    <div class="col-md-5">
                        <div class="card border-0 rounded-4 p-3 shadow-sm h-100 bg-secondary text-white">
                            <h6 class="fw-bold mb-3"><i class="bi bi-cpu me-1"></i> AI Consumption Estimator</h6>
                            <div class="mb-3">
                                <small class="text-white-50 d-block" style="font-size: 11px;">AI Predicted Fuel</small>
                                <h3 class="fw-bold text-info" id="simPredictedFuelM">0.0 kWh</h3>
                            </div>
                            <div class="mb-3">
                                <small class="text-white-50 d-block" style="font-size: 11px;">Estimated Actual Consumption</small>
                                <h3 class="fw-bold text-warning" id="simRealtimeFuelM">0.0 kWh</h3>
                            </div>
                            <div class="border-top border-secondary pt-3 mt-3">
                                <h6 class="fw-bold" style="font-size: 13px;">Safety Recommendations:</h6>
                                <ul class="ps-3 mb-0 text-white-50" style="font-size: 11.5px;">
                                    <li>Optimal driving speed: 60-80 km/h.</li>
                                    <li>Severe engine idling wastes 0.05L/min.</li>
                                    <li>Aggressive driving reduces safety score.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light d-flex justify-content-between">
                <div>
                    <button class="btn btn-outline-danger btn-sm rounded-3 me-2" id="btnTriggerAggressive" onclick="triggerAggressiveAction();" disabled>
                        <i class="bi bi-lightning-charge-fill"></i> Trigger Aggressive Speeding
                    </button>
                    <button class="btn btn-outline-warning btn-sm rounded-3" id="btnTriggerHarshBrake" onclick="triggerHarshBrakeAction();" disabled>
                        <i class="bi bi-x-circle-fill"></i> Trigger Harsh Brake
                    </button>
                </div>
                <button class="btn btn-premium rounded-3" id="btnSimulateControl" onclick="startGpsStreaming();">
                    <i class="bi bi-play-circle-fill me-1"></i> Run Telemetry Sim
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal complete trip forms -->
<div class="modal fade" id="completeTripModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog rounded-4 overflow-hidden">
        <div class="modal-content border-0 shadow">
            <form id="completeTripForm" method="POST">
                @csrf
                <input type="hidden" name="start_location" id="finalStartLocation" value="Manila Hub (Port Area)">
                <input type="hidden" name="end_location" id="finalEndLocation" value="Makati Hub (Ayala Ave)">
                <input type="hidden" name="distance_km" id="finalDistanceKm" value="9.5">
                
                <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #10B981 0%, #064E3B 100%);">
                    <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i> Finalize and Complete Ride</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success bg-success bg-opacity-10 border-0 rounded-3 mb-3 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-success"><i class="bi bi-geo-alt-fill me-1"></i> Destination Reached:</span>
                            <span class="badge bg-success" id="finalRouteBadge">Completed</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Actual Energy Consumed (kWh)</label>
                        <input type="number" step="0.01" name="actual_fuel_liters" id="finalFuelLiters" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Actual Trip Duration (Minutes)</label>
                        <input type="number" name="actual_duration_minutes" id="finalDurationMinutes" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-3 fw-bold px-4" style="background: #10B981 !important;">
                        <i class="bi bi-flag-fill me-1"></i> Save Log & Complete Trip
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Toggle manual and auto assignment forms
    const autoSwitch = document.getElementById('autoAssignSwitch');
    if (autoSwitch) {
        autoSwitch.addEventListener('change', function() {
            const manualFields = document.getElementById('manualAssignmentFields');
            if (manualFields) {
                if (this.checked) {
                    manualFields.classList.add('d-none');
                    document.querySelectorAll('#manualAssignmentFields select').forEach(select => select.removeAttribute('required'));
                } else {
                    manualFields.classList.remove('d-none');
                    document.querySelectorAll('#manualAssignmentFields select').forEach(select => select.setAttribute('required', 'required'));
                }
            }
        });
    }

    // Handle AJAX preview of routes and fuel prediction
    const startSelect = document.getElementById('start_location');
    const endSelect = document.getElementById('end_location');
    const typeSelect = document.getElementById('vehicle_type');
    const fuelSelect = document.getElementById('fuel_type');

    function checkRoutePreview() {
        if (!startSelect || !endSelect || !typeSelect) return;
        const start = startSelect.value;
        const end = endSelect.value;
        const type = typeSelect.value;
        const fuelType = fuelSelect ? fuelSelect.value : 'gasoline';

        if (start && end) {
            if (start === end) {
                alert("Start and destination hubs cannot be the same.");
                endSelect.value = "";
                return;
            }

            // Immediately enable Dispatch button & set fallback metrics
            const dKm = document.getElementById('distance_km'); if (dKm) dKm.value = "14.5";
            const eDur = document.getElementById('estimated_duration_minutes'); if (eDur) eDur.value = "28";
            const eFuel = document.getElementById('estimated_fuel_liters'); if (eFuel) eFuel.value = "2.4";
            const btnSub = document.getElementById('btnSubmitTrip'); if (btnSub) btnSub.removeAttribute('disabled');

            // Perform fetch call to get preview path and AI estimation
            fetch("{{ route('trips.plan-preview') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ start, end, vehicle_type: type, fuel_type: fuelType })
            })
            .then(res => res.json())
            .then(data => {
                if (data.routes && data.routes.length > 0) {
                    const bestRoute = data.routes[0];

                    if (dKm) dKm.value = bestRoute.distance_km;
                    if (eDur) eDur.value = bestRoute.duration_minutes;
                    if (eFuel) eFuel.value = bestRoute.estimated_fuel;

                    const pDist = document.getElementById('previewDistance'); if (pDist) pDist.innerText = bestRoute.distance_km + " km";
                    const unitLabel = bestRoute.fuel_unit || (fuelType === 'electric' ? 'kWh (EV)' : (fuelType === 'diesel' ? 'Liters (Diesel)' : 'Liters (Gas)'));
                    const pFuel = document.getElementById('previewFuel'); if (pFuel) pFuel.innerText = bestRoute.estimated_fuel + " " + unitLabel;
                    const pDur = document.getElementById('previewDuration'); if (pDur) pDur.innerText = bestRoute.duration_minutes + " mins";
                    
                    const congestionElement = document.getElementById('previewTraffic') || document.getElementById('previewCongestion');
                    if (congestionElement) {
                        congestionElement.innerText = bestRoute.congestion || 'Normal';
                        if (bestRoute.congestion === 'Heavy') {
                            congestionElement.className = "fw-bold text-danger";
                        } else if (bestRoute.congestion === 'Moderate') {
                            congestionElement.className = "fw-bold text-warning";
                        } else {
                            congestionElement.className = "fw-bold text-success";
                        }
                    }

                    const ecoBadge = document.getElementById('ecoBadge');
                    if (ecoBadge) {
                        if (bestRoute.is_eco) ecoBadge.classList.remove('d-none');
                        else ecoBadge.classList.add('d-none');
                    }

                    const suggestionsPanel = document.getElementById('previewRecommendation') || document.getElementById('routingSuggestions');
                    if (suggestionsPanel) {
                        if (bestRoute.congestion === 'Heavy') {
                            suggestionsPanel.innerHTML = `<i class="bi bi-info-circle"></i> High idling risk. AI estimates +15% fuel spike due to traffic.`;
                        } else {
                            suggestionsPanel.innerHTML = `<i class="bi bi-check-circle-fill"></i> Clear road. Drivers can cruise at optimal speeds for maximum efficiency.`;
                        }
                    }

                    const card = document.getElementById('routePreviewCard');
                    if (card) card.classList.remove('d-none');

                    if (btnSub) btnSub.removeAttribute('disabled');
                }
            })
            .catch(err => console.error(err));
        }
    }

    if (startSelect) startSelect.addEventListener('change', checkRoutePreview);
    if (endSelect) endSelect.addEventListener('change', checkRoutePreview);
    if (typeSelect) typeSelect.addEventListener('change', checkRoutePreview);
    if (fuelSelect) fuelSelect.addEventListener('change', checkRoutePreview);


    // ==========================================
    // GPS / Telemetry Live Simulator Logic
    // ==========================================
    let simTripId = null;
    let simStart = "";
    let simEnd = "";
    let simDistance = 0.0;
    let simPredictedFuel = 0.0;
    let simVehicleType = "";

    // Leaflet Live GPS Map Objects (Main Page)
    let leafletMap = null;
    let carMarker = null;
    let polylineTrail = null;

    // Leaflet Live GPS Map Objects (Modal)
    let leafletMapModal = null;
    let carMarkerModal = null;
    let polylineTrailModal = null;

    // Comprehensive Philippine Transit Hubs Database
    const hubCoords = {
        // Metro Manila Hubs
        'Manila Hub (Port Area)': [14.5880, 120.9650],
        'Manila': [14.5995, 120.9842],
        'Intramuros': [14.5895, 120.9750],
        'Ermita': [14.5800, 120.9830],
        'Malate': [14.5700, 120.9900],
        'Makati Hub (Ayala Ave)': [14.5547, 121.0244],
        'Makati': [14.5547, 121.0244],
        'BGC Taguig Hub (9th Ave)': [14.5515, 121.0510],
        'BGC': [14.5515, 121.0510],
        'Taguig': [14.5176, 121.0509],
        'Pasay Hub (MOA Complex)': [14.5352, 120.9820],
        'Pasay': [14.5378, 120.9993],
        'MOA': [14.5352, 120.9820],
        'Quezon City Hub (Cubao)': [14.6178, 121.0572],
        'Quezon City': [14.6760, 121.0437],
        'Cubao': [14.6178, 121.0572],
        'Diliman': [14.6538, 121.0685],
        'NAIA Terminal 3 Hub': [14.5186, 121.0125],
        'NAIA Terminal 1': [14.5086, 121.0025],
        'NAIA': [14.5204, 121.0134],
        'Airport': [14.5204, 121.0134],
        'Alabang Hub (Filinvest)': [14.4170, 121.0410],
        'Alabang': [14.4172, 121.0408],
        'Muntinlupa': [14.4081, 121.0415],
        'Ortigas Hub (Ortigas Center)': [14.5869, 121.0614],
        'Ortigas': [14.5869, 121.0614],
        'Pasig': [14.5764, 121.0851],
        'Mandaluyong': [14.5794, 121.0359],
        'San Juan': [14.6019, 121.0355],
        'Greenhills': [14.6019, 121.0515],
        'Caloocan': [14.6571, 120.9841],
        'Monumento': [14.6571, 120.9841],
        'Marikina': [14.6507, 121.1029],
        'Paranaque': [14.4793, 121.0198],
        'Las Pinas': [14.4445, 120.9939],
        'Valenzuela': [14.7011, 120.9830],
        
        // Metro Davao Hubs (Hirna Origin Hubs)
        'Davao City Hub': [7.0707, 125.6087],
        'Davao': [7.0707, 125.6087],
        'Davao Airport': [7.1253, 125.6456],
        'Ecoland Davao': [7.0543, 125.5925],
        'Matina Hub': [7.0601, 125.5872],
        'Bajada Davao': [7.0864, 125.6148],
        'Lanang Hub': [7.1039, 125.6367],
        
        // Metro Cebu Hubs
        'Cebu City Hub': [10.3157, 123.8854],
        'Cebu': [10.3157, 123.8854],
        'IT Park Cebu': [10.3298, 123.9061],
        'Mactan Airport': [10.3075, 123.9794]
    };

    function getLatLng(name, fallback) {
        if (!name) return fallback || [14.5995, 120.9842];
        const trimmed = name.trim();
        if (hubCoords[trimmed]) return hubCoords[trimmed];
        
        // Substring / fuzzy search
        const lower = trimmed.toLowerCase();
        for (let key in hubCoords) {
            if (lower.includes(key.toLowerCase()) || key.toLowerCase().includes(lower)) {
                return hubCoords[key];
            }
        }
        
        // Deterministic hash fallback to guarantee distinct realistic coordinates for any custom location
        let hash = 0;
        for (let i = 0; i < lower.length; i++) {
            hash = (hash << 5) - hash + lower.charCodeAt(i);
            hash |= 0;
        }
        const latOffset = ((Math.abs(hash) % 100) / 100) * 0.12 - 0.06; // +/- 0.06 deg
        const lngOffset = (((Math.abs(hash >> 3)) % 100) / 100) * 0.12 - 0.06;
        
        return [14.5500 + latOffset, 121.0200 + lngOffset];
    }

    // Simulated path arrays
    let simulatedPath = [];
    let currentStepIndex = 0;
    let activeSimulationInterval = null;
    let safetyScoreTracker = 100;
    
    // Telemetry tracking stats
    let totalIdleSeconds = 0;
    let computedTripFuel = 0.0;
    let currentVehicleSpeed = 50; 
    let aggressiveSpeedTrigger = false;
    let harshBrakeTrigger = false;

    document.addEventListener("DOMContentLoaded", function() {
        simStart = 'Manila Hub (Port Area)';
        simEnd = 'Makati Hub (Ayala Ave)';
        simDistance = 9.5;
        simPredictedFuel = 3.8;

        // Move modal to body to prevent container context clipping
        const simModalElement = document.getElementById('telemetrySimulatorModal');
        if (simModalElement && simModalElement.parentNode !== document.body) {
            document.body.appendChild(simModalElement);
        }

        initLeafletGpsMap(simStart, simEnd);
        generateSimulatedPathCoordinates(simStart, simEnd);

        // Global Bootstrap modal listener to initialize map tiles ONCE modal is fully visible
        if (simModalElement) {
            simModalElement.addEventListener('shown.bs.modal', function () {
                initModalMap(simStart || 'Manila Hub (Port Area)', simEnd || 'Makati Hub (Ayala Ave)');
            });
        }

        const startSelect = document.getElementById('start_location');
        const endSelect = document.getElementById('end_location');

        if (startSelect && endSelect) {
            startSelect.addEventListener('change', function() {
                const s = startSelect.value;
                const e = endSelect.value || 'Makati';
                if (s && e) {
                    simStart = s;
                    simEnd = e;
                    initLeafletGpsMap(s, e);
                    generateSimulatedPathCoordinates(s, e);
                }
            });
            endSelect.addEventListener('change', function() {
                const s = startSelect.value || 'Manila';
                const e = endSelect.value;
                if (s && e) {
                    simStart = s;
                    simEnd = e;
                    initLeafletGpsMap(s, e);
                    generateSimulatedPathCoordinates(s, e);
                }
            });
        }
    });

    function initLeafletGpsMap(startName, endName) {
        const mapContainer = document.getElementById('liveGpsMapMain');
        if (!mapContainer) return;

        const startLatLng = getLatLng(startName) || [14.5995, 120.9842];
        const endLatLng = getLatLng(endName) || [14.5547, 121.0244];

        if (leafletMap) {
            try { leafletMap.remove(); } catch(e) {}
            leafletMap = null;
        }

        mapContainer._leaflet_id = null;

        try {
            leafletMap = L.map('liveGpsMapMain', { zoomControl: true }).setView(startLatLng, 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap &bull; Hirna Mobility Telemetry'
            }).addTo(leafletMap);

            // Start & Destination Hub Markers
            L.circleMarker(startLatLng, { color: '#10B981', radius: 8, fillColor: '#10B981', fillOpacity: 0.9 }).addTo(leafletMap).bindPopup("<b>Start Hub:</b> " + startName);
            L.circleMarker(endLatLng, { color: '#EF4444', radius: 8, fillColor: '#EF4444', fillOpacity: 0.9 }).addTo(leafletMap).bindPopup("<b>Destination:</b> " + endName);

            // Progressive Polyline Trail
            polylineTrail = L.polyline([startLatLng], { color: '#10B981', weight: 4, opacity: 0.9 }).addTo(leafletMap);

            // Custom Leaflet EV Icon
            const carIcon = L.divIcon({
                className: 'custom-ev-marker',
                html: `<div style="background:#064E3B; border:2px solid #10B981; border-radius:50%; width:34px; height:34px; display:flex; align-items:center; justify-content:center; box-shadow:0 0 14px rgba(16,185,129,0.9);"><i class="bi bi-ev-front-fill text-white fs-6"></i></div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            carMarker = L.marker(startLatLng, { icon: carIcon }).addTo(leafletMap).bindPopup("<b>Hirna Vehicle Live Position</b>");

            try {
                leafletMap.fitBounds([startLatLng, endLatLng], { padding: [35, 35] });
            } catch(e) {}

            [100, 300, 500, 800].forEach(delay => {
                setTimeout(() => {
                    if (leafletMap) {
                        leafletMap.invalidateSize();
                        try { leafletMap.fitBounds([startLatLng, endLatLng], { padding: [35, 35] }); } catch(e) {}
                    }
                }, delay);
            });
        } catch(err) {
            console.error("Leaflet map initialization error:", err);
        }
    }

    // Dedicated map initializer for the MODAL container (liveGpsMapModal)
    function initModalMap(startName, endName) {
        const mapContainer = document.getElementById('liveGpsMapModal');
        if (!mapContainer) return;

        mapContainer.style.height = '230px';
        mapContainer.style.minHeight = '230px';

        const startLatLng = getLatLng(startName) || [14.5995, 120.9842];
        const endLatLng = getLatLng(endName) || [14.5547, 121.0244];

        if (leafletMapModal) {
            try { leafletMapModal.remove(); } catch(e) {}
            leafletMapModal = null;
        }
        mapContainer._leaflet_id = null;

        try {
            leafletMapModal = L.map('liveGpsMapModal', { zoomControl: true }).setView(startLatLng, 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap &bull; Hirna Modal GPS'
            }).addTo(leafletMapModal);

            // Start & Destination Hub Markers
            L.circleMarker(startLatLng, { color: '#10B981', radius: 8, fillColor: '#10B981', fillOpacity: 0.9 }).addTo(leafletMapModal).bindPopup("<b>Start Hub:</b> " + startName);
            L.circleMarker(endLatLng, { color: '#EF4444', radius: 8, fillColor: '#EF4444', fillOpacity: 0.9 }).addTo(leafletMapModal).bindPopup("<b>Destination:</b> " + endName);

            // Polyline Trail (Progressive breadcrumbs)
            polylineTrailModal = L.polyline([startLatLng], { color: '#10B981', weight: 5, opacity: 0.95 }).addTo(leafletMapModal);

            // Custom Leaflet EV Icon with Pulsing Radar Effect
            const carIcon = L.divIcon({
                className: 'custom-ev-marker-modal',
                html: `<div style="position:relative; width:36px; height:36px;">
                    <div style="position:absolute; top:-4px; left:-4px; width:44px; height:44px; background:rgba(16,185,129,0.35); border-radius:50%; animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;"></div>
                    <div style="background:#064E3B; border:2px solid #10B981; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; box-shadow:0 0 16px rgba(16,185,129,0.9); position:relative; z-index:2;">
                        <i class="bi bi-ev-front-fill text-white fs-6"></i>
                    </div>
                </div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            carMarkerModal = L.marker(startLatLng, { icon: carIcon }).addTo(leafletMapModal).bindPopup("<b>Hirna Vehicle Live Position</b>");

            try {
                leafletMapModal.fitBounds([startLatLng, endLatLng], { padding: [30, 30] });
            } catch(e) {}

            [100, 250, 500, 800].forEach(delay => {
                setTimeout(() => {
                    if (leafletMapModal) {
                        leafletMapModal.invalidateSize();
                        try { leafletMapModal.fitBounds([startLatLng, endLatLng], { padding: [30, 30] }); } catch(e) {}
                    }
                }, delay);
            });
        } catch(err) {
            console.error("Modal Leaflet map initialization error:", err);
        }
    }

    function launchTelemetrySimulator(tripId, start, end, type, distance, predictedFuel) {
        simTripId = tripId || 999;
        simStart = start || 'Manila Hub (Port Area)';
        simEnd = end || 'Makati Hub (Ayala Ave)';
        simDistance = parseFloat(distance) || 9.5;
        simPredictedFuel = parseFloat(predictedFuel) || 3.8;
        simVehicleType = type || 'Sedan';

        // Reset variables
        currentStepIndex = 0;
        totalIdleSeconds = 0;
        computedTripFuel = 0.0;
        safetyScoreTracker = 100;
        aggressiveSpeedTrigger = false;
        harshBrakeTrigger = false;
        if (activeSimulationInterval) {
            clearInterval(activeSimulationInterval);
            activeSimulationInterval = null;
        }

        // Update BOTH main-page and modal elements
        const idsToUpdate = {
            'simStartHub': simStart, 'simStartHubM': simStart,
            'simEndHub': simEnd, 'simEndHubM': simEnd
        };
        Object.entries(idsToUpdate).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.innerText = val;
        });

        // Reset progress bars
        ['simProgressBar', 'simProgressBarM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.width = "0%";
        });

        // Reset predicted fuel
        ['simPredictedFuel', 'simPredictedFuelM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = simPredictedFuel.toFixed(2) + " kWh";
        });

        // Reset realtime fuel
        ['simRealtimeFuel', 'simRealtimeFuelM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = "0.00 kWh";
        });

        // Reset speed
        ['simSpeed', 'simSpeedM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = "0 <span class='fs-6 fw-normal'>km/h</span>";
        });

        // Reset idle
        ['simIdle', 'simIdleM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = "0s";
        });

        // Reset safety score
        ['simSafetyScore', 'simSafetyScoreM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = "100%";
        });

        // Reset feed
        ['telemetryFeed', 'telemetryFeedM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = "<span class='text-muted text-center py-4'>Ready to run simulation stream. Click 'Run Telemetry Sim' below.</span>";
        });

        // Reset control buttons
        const btnModal = document.getElementById('btnSimulateControl');
        const btnMain = document.getElementById('btnSimulateControlMain');
        [btnModal, btnMain].forEach(b => {
            if (b) {
                b.innerHTML = "<i class='bi bi-play-circle-fill me-1'></i> Run Telemetry Sim";
                b.removeAttribute('disabled');
            }
        });

        // Generate coordinate path for simulation
        generateSimulatedPathCoordinates(simStart, simEnd);

        // Re-initialize both maps with the new route
        initLeafletGpsMap(simStart, simEnd);
        initModalMap(simStart, simEnd);
    }

    function generateSimulatedPathCoordinates(startName, endName) {
        simulatedPath = [];
        let startLatLng = getLatLng(startName, [14.5995, 120.9842]);
        let endLatLng = getLatLng(endName, [14.5547, 121.0244]);

        // Guarantee start and destination coordinates are distinct points
        if (Math.abs(startLatLng[0] - endLatLng[0]) < 0.004 && Math.abs(startLatLng[1] - endLatLng[1]) < 0.004) {
            endLatLng = [startLatLng[0] - 0.042, startLatLng[1] + 0.038];
        }
        
        const steps = 28; // 28 realistic road progression steps

        // Midpoint curve for natural road trajectory (Bezier curve)
        const midLat = (startLatLng[0] + endLatLng[0]) / 2 + (startLatLng[1] - endLatLng[1]) * 0.12;
        const midLng = (startLatLng[1] + endLatLng[1]) / 2 + (endLatLng[0] - startLatLng[0]) * 0.12;

        for (let i = 0; i <= steps; i++) {
            let t = i / steps;
            let lat = (1 - t) * (1 - t) * startLatLng[0] + 2 * (1 - t) * t * midLat + t * t * endLatLng[0];
            let lng = (1 - t) * (1 - t) * startLatLng[1] + 2 * (1 - t) * t * midLng + t * t * endLatLng[1];

            // Micro-jitter for real GPS noise (except at exact start and end)
            if (i > 0 && i < steps) {
                lat += (Math.sin(i * 1.8) * 0.00025);
                lng += (Math.cos(i * 1.8) * 0.00025);
            }

            simulatedPath.push({ lat: lat, lng: lng });
        }
    }

    function startGpsStreaming() {
        const btn = document.getElementById('btnSimulateControl');
        const btnMain = document.getElementById('btnSimulateControlMain');
        if (activeSimulationInterval) {
            // Pause
            clearInterval(activeSimulationInterval);
            activeSimulationInterval = null;
            [btn, btnMain].forEach(b => { if (b) b.innerHTML = "<i class='bi bi-play-circle-fill me-1'></i> Resume Sim"; });
            ['btnTriggerAggressive', 'btnTriggerAggressiveMain'].forEach(id => {
                const el = document.getElementById(id); if (el) el.setAttribute('disabled', 'disabled');
            });
            ['btnTriggerHarshBrake', 'btnTriggerHarshBrakeMain'].forEach(id => {
                const el = document.getElementById(id); if (el) el.setAttribute('disabled', 'disabled');
            });
        } else {
            // Ensure path exists
            if (!simStart) simStart = 'Manila Hub (Port Area)';
            if (!simEnd) simEnd = 'Makati Hub (Ayala Ave)';
            if (!simulatedPath || simulatedPath.length === 0) {
                generateSimulatedPathCoordinates(simStart, simEnd);
            }
            if (currentStepIndex >= simulatedPath.length) {
                currentStepIndex = 0;
            }

            // Start
            [btn, btnMain].forEach(b => { if (b) b.innerHTML = "<i class='bi bi-pause-circle-fill me-1'></i> Pause Sim"; });
            ['btnTriggerAggressive', 'btnTriggerAggressiveMain'].forEach(id => {
                const el = document.getElementById(id); if (el) el.removeAttribute('disabled');
            });
            ['btnTriggerHarshBrake', 'btnTriggerHarshBrakeMain'].forEach(id => {
                const el = document.getElementById(id); if (el) el.removeAttribute('disabled');
            });

            // Clear placeholder text on first start
            if (currentStepIndex === 0) {
                ['telemetryFeed', 'telemetryFeedM'].forEach(id => {
                    const el = document.getElementById(id); if (el) el.innerHTML = "";
                });
            }

            streamTelemetryStep(); // Immediately execute step 1!
            activeSimulationInterval = setInterval(streamTelemetryStep, 1000); // send GPS logs every 1.0s
        }
    }

    function triggerAggressiveAction() {
        aggressiveSpeedTrigger = true;
        addSimFeedEvent("🚨 Dispatcher trigger: Aggressive Throttle (Speeding Alert)", "danger");
    }

    function triggerHarshBrakeAction() {
        harshBrakeTrigger = true;
        addSimFeedEvent("⚠️ Dispatcher trigger: Sudden Harsh Braking Event", "warning");
    }

    function addSimFeedEvent(message, statusClass) {
        const timeString = new Date().toLocaleTimeString();
        const icon = statusClass === 'danger' ? 'bi-exclamation-triangle-fill' : (statusClass === 'warning' ? 'bi-x-octagon' : (statusClass === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle'));
        const item = `
            <div class="list-group-item text-${statusClass} px-0 border-0 border-bottom d-flex justify-content-between py-2">
                <span><i class="bi ${icon} me-1"></i> ${message}</span>
                <small class="text-muted">${timeString}</small>
            </div>
        `;
        // Update BOTH main-page and modal feeds
        ['telemetryFeed', 'telemetryFeedM'].forEach(id => {
            const feed = document.getElementById(id);
            if (feed) feed.insertAdjacentHTML('afterbegin', item);
        });
    }

    function streamTelemetryStep() {
        if (currentStepIndex >= simulatedPath.length) {
            // Finished path
            clearInterval(activeSimulationInterval);
            activeSimulationInterval = null;

            // Reset buttons
            const btnModal = document.getElementById('btnSimulateControl');
            const btnMain = document.getElementById('btnSimulateControlMain');
            [btnModal, btnMain].forEach(b => {
                if (b) {
                    b.innerHTML = "<i class='bi bi-arrow-counterclockwise me-1'></i> Restart Sim";
                    b.removeAttribute('disabled');
                }
            });

            // Set progress to 100%
            ['simProgressBar', 'simProgressBarM'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.width = "100%";
            });

            addSimFeedEvent(`🏁 Route Completed! Destination ${simEnd} reached successfully. Final Safety Score: ${safetyScoreTracker}%.`, "success");

            if (simTripId && simTripId !== 999) {
                setTimeout(showCompleteFormModal, 1500);
            }
            return;
        }

        const point = simulatedPath[currentStepIndex];

        // Update MAIN PAGE Leaflet Map Marker Position & Polyline smoothly
        try {
            if (carMarker) {
                carMarker.setLatLng([point.lat, point.lng]);
                if (leafletMap && currentStepIndex % 3 === 0) {
                    leafletMap.panTo([point.lat, point.lng], { animate: true, duration: 0.4 });
                }
            }
            if (polylineTrail) {
                polylineTrail.addLatLng([point.lat, point.lng]);
            }
        } catch (e) {
            console.warn("Main map animation frame bypassed:", e);
        }

        // Update MODAL Leaflet Map Marker Position & Polyline smoothly
        try {
            if (carMarkerModal) {
                carMarkerModal.setLatLng([point.lat, point.lng]);
                if (leafletMapModal && currentStepIndex % 3 === 0) {
                    leafletMapModal.panTo([point.lat, point.lng], { animate: true, duration: 0.4 });
                }
            }
            if (polylineTrailModal) {
                polylineTrailModal.addLatLng([point.lat, point.lng]);
            }
        } catch (e) {
            console.warn("Modal map animation frame bypassed:", e);
        }

        // Randomize speed based on triggers
        let speed = 42 + Math.floor(Math.random() * 22); // standard: 42-64 km/h
        let idleSec = 0;
        let isHarsh = false;

        if (aggressiveSpeedTrigger) {
            speed = 92 + Math.floor(Math.random() * 18); // speeding: 92-110 km/h
            aggressiveSpeedTrigger = false;
        } else if (harshBrakeTrigger) {
            speed = 8 + Math.floor(Math.random() * 8); // harsh braking: 8-16 km/h
            isHarsh = true;
            harshBrakeTrigger = false;
        } else if (Math.random() > 0.88) {
            // Idle event
            speed = 0;
            idleSec = 15;
            totalIdleSeconds += 15;
        }

        currentVehicleSpeed = speed;

        // Cumulative fuel estimation
        let baseBurnRate = 0.08;
        let speedDragMultiplier = 1.0;
        if (speed > 90) speedDragMultiplier = 1.25;
        if (speed === 0) {
            computedTripFuel += 0.015; // idle fuel burn
        } else {
            computedTripFuel += ((simDistance / simulatedPath.length) * baseBurnRate) * speedDragMultiplier;
        }

        // Display updates on BOTH main-page and modal
        ['simSpeed', 'simSpeedM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = `${speed} <span class="fs-6 fw-normal">km/h</span>`;
        });
        ['simIdle', 'simIdleM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = `${totalIdleSeconds}s`;
        });
        ['simRealtimeFuel', 'simRealtimeFuelM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = computedTripFuel.toFixed(2) + " kWh";
        });

        // Progress update on both
        const pct = Math.round(((currentStepIndex + 1) / simulatedPath.length) * 100);
        ['simProgressBar', 'simProgressBarM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.width = pct + "%";
        });

        // Call backend API to record logs dynamically
        if (simTripId && simTripId !== 999) {
            fetch(`{{ url('/') }}/trips/${simTripId}/simulate-gps`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    lat: point.lat,
                    lng: point.lng,
                    speed: speed,
                    idle_seconds: idleSec,
                    is_harsh_braking: isHarsh
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    safetyScoreTracker = data.current_safety_score;
                    updateSafetyScoreBoth(safetyScoreTracker);

                    if (speed > 80) {
                        addSimFeedEvent(`🚨 Speed Violation: ${speed} km/h recorded. Safety score: ${safetyScoreTracker}%.`, "danger");
                    } else if (isHarsh) {
                        addSimFeedEvent(`⚠️ Safety Trigger: Harsh Braking detected. Safety score: ${safetyScoreTracker}%.`, "warning");
                    } else if (speed === 0) {
                        addSimFeedEvent(`🚦 Traffic Idle: Hirna Vehicle idling at intersection (+15s).`, "warning");
                    } else {
                        addSimFeedEvent(`📡 GPS: Lat ${point.lat.toFixed(4)}, Lng ${point.lng.toFixed(4)} | Speed ${speed} km/h`, "secondary");
                    }
                } else {
                    logDemoStepFeed(speed, isHarsh, idleSec, point);
                }
            })
            .catch(() => {
                logDemoStepFeed(speed, isHarsh, idleSec, point);
            });
        } else {
            logDemoStepFeed(speed, isHarsh, idleSec, point);
        }

        // CRITICAL FIX: Increment step counter to advance the car marker!
        currentStepIndex++;
    }

    // Helper to update safety score on both main-page and modal
    function updateSafetyScoreBoth(score) {
        ['simSafetyScore', 'simSafetyScoreM'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = score + "%";
        });
    }

    function logDemoStepFeed(speed, isHarsh, idleSec, point) {
        if (speed > 80) {
            safetyScoreTracker = Math.max(60, safetyScoreTracker - 5);
            addSimFeedEvent(`🚨 Speed Violation: ${speed} km/h recorded. Safety score: ${safetyScoreTracker}%.`, "danger");
        } else if (isHarsh) {
            safetyScoreTracker = Math.max(60, safetyScoreTracker - 3);
            addSimFeedEvent(`⚠️ Harsh Braking Event: Sudden deceleration recorded.`, "warning");
        } else if (speed === 0) {
            addSimFeedEvent(`🚦 Traffic Idle: Hirna Vehicle idling at intersection (+15s).`, "warning");
        } else {
            addSimFeedEvent(`📡 GPS: Lat ${point.lat.toFixed(4)}, Lng ${point.lng.toFixed(4)} | Speed ${speed} km/h`, "secondary");
        }
        updateSafetyScoreBoth(safetyScoreTracker);
    }

    function showCompleteFormModal() {
        // Hide Telemetry Modal
        const telemetryModal = bootstrap.Modal.getInstance(document.getElementById('telemetrySimulatorModal'));
        if (telemetryModal) telemetryModal.hide();

        // Populate and open complete trip modal
        const finalFuel = Math.max(0.2, computedTripFuel);
        const finalDuration = Math.max(1, Math.round((simDistance / 45) * 60) + Math.round(totalIdleSeconds / 60));

        const fuelInput = document.getElementById('finalFuelLiters');
        if (fuelInput) fuelInput.value = finalFuel.toFixed(2);
        
        const durationInput = document.getElementById('finalDurationMinutes');
        if (durationInput) durationInput.value = finalDuration;

        const startInput = document.getElementById('finalStartLocation');
        if (startInput) startInput.value = simStart || 'Manila Hub (Port Area)';

        const endInput = document.getElementById('finalEndLocation');
        if (endInput) endInput.value = simEnd || 'Makati Hub (Ayala Ave)';

        const distInput = document.getElementById('finalDistanceKm');
        if (distInput) distInput.value = simDistance || 9.5;

        const routeBadge = document.getElementById('finalRouteBadge');
        if (routeBadge) routeBadge.innerText = (simStart || 'Origin') + ' → ' + (simEnd || 'Destination');

        // Set action route on form dynamically with full base URL
        const form = document.getElementById('completeTripForm');
        if (form) {
            if (simTripId && simTripId !== 999) {
                form.action = `{{ url('/') }}/trips/${simTripId}/complete`;
            } else {
                form.action = "{{ route('trips.complete-demo') }}";
            }
        }

        const completeModalElement = document.getElementById('completeTripModal');
        if (completeModalElement && completeModalElement.parentNode !== document.body) {
            document.body.appendChild(completeModalElement);
        }
        const completeModal = bootstrap.Modal.getOrCreateInstance(completeModalElement);
        completeModal.show();
    }

    function filterDriverLeaderboard(filter, btn) {
        document.querySelectorAll('#driverLeaderboardTable tbody tr.driver-row').forEach(row => {
            if (filter === 'all' || row.getAttribute('data-status') === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        if (btn) {
            btn.parentElement.querySelectorAll('.btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    }

    function exportTripsToCSV() {
        let csv = [];
        const rows = document.querySelectorAll("table tr");
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            for (let j = 0; j < cols.length; j++) 
                row.push('"' + cols[j].innerText.replace(/"/g, '""').trim() + '"');
            csv.push(row.join(","));
        }

        const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        const downloadLink = document.createElement("a");
        downloadLink.download = "Hirna_Driver_and_Trip_Dispatches.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
    function showCompletedTripModal(ref, driver, vehicle, plate, origin, dest, dist, duration, kwh, score) {
        document.getElementById('rcptRef').innerText = ref;
        document.getElementById('rcptDriver').innerText = driver;
        document.getElementById('rcptVehicle').innerText = vehicle + (plate && plate !== 'N/A' ? " (" + plate + ")" : "");
        document.getElementById('rcptRoute').innerText = origin + " → " + dest;
        document.getElementById('rcptDist').innerText = dist;
        document.getElementById('rcptTime').innerText = duration;
        document.getElementById('rcptKwh').innerText = kwh;
        document.getElementById('rcptScore').innerText = score;

        const modalElem = document.getElementById('completedTripReceiptModal');
        if (modalElem && modalElem.parentNode !== document.body) {
            document.body.appendChild(modalElem);
        }
        const modal = bootstrap.Modal.getOrCreateInstance(modalElem);
        modal.show();
    }

    function exportCompletedTripsCSV() {
        const rows = [
            ["Trip Ref", "Date", "Driver Name", "License Number", "Hirna Vehicle Model", "License Plate", "Origin Hub", "Destination Hub", "Distance (km)", "Trip Duration (min)", "Energy Consumed (kWh)", "Charging Expense (PHP)", "Driver Safety Score"],
            ["#TRP-9082", "2026-08-16 16:45", "Juan Dela Cruz", "N01-18-99201", "Hyundai Accent Hirna Taxi", "TXI-9876", "Manila Hub (Port Area)", "Makati Hub (Ayala Ave)", "9.5", "22", "3.8", "43.70", "98%"],
            ["#TRP-8910", "2026-08-16 14:10", "Marco Santos", "N02-19-44812", "Toyota Vios Hirna Taxi", "TXI-5421", "BGC Hub (Market Market)", "Quezon City Hub (Cubao)", "14.2", "35", "5.2", "59.80", "94%"],
            ["#TRP-8744", "2026-08-15 11:30", "Ramon Fernandez", "N03-20-11029", "Nissan Almera Hirna Taxi", "TXI-1122", "Pasay Hub (MOA Complex)", "NAIA Terminal 3 Hub", "7.8", "18", "2.9", "33.35", "91%"],
            ["#TRP-8601", "2026-08-15 09:15", "Gabriel Alonzo", "N04-21-77391", "Toyota HiAce Shuttle Van", "VAN-4509", "Alabang Hub (Filinvest)", "Ortigas Hub (Ortigas Center)", "21.0", "45", "8.4", "96.60", "88%"]
        ];

        let csvContent = "data:text/csv;charset=utf-8," + rows.map(e => e.join(",")).join("\n");
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "Completed_Driver_Trips_History_Report.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<!-- Import Trips CSV Modal -->
<div class="modal fade" id="importTripsCsvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog rounded-4 overflow-hidden">
        <div class="modal-content border-0">
            <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module_type" value="trips">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Trip Dispatches (CSV)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Select CSV File (.csv)</label>
                        <input type="file" name="csv_file" accept=".csv, .txt" class="form-control rounded-3" required>
                        <small class="text-muted mt-1 d-block">Expected columns: Start Hub, Destination, Distance (km), Estimated kWh, Duration (min).</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-cloud-upload me-1"></i> Import Trips CSV</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
