@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-danger text-white px-3 py-1 rounded-pill" style="font-size: 11px; letter-spacing: 0.5px; background: #CE2029 !important;">HIRNA MOBILITY SOLUTIONS INC.</span>
            <span class="text-muted" style="font-size: 12px;"><i class="bi bi-compass text-danger me-1"></i> Route Planning & Optimization</span>
        </div>
        <h2 class="page-header-title mt-1">Route Planning and Optimization</h2>
        <p class="page-header-subtitle">Plan eco-friendly Hirna routes, analyze traffic delays across Metro Manila & Davao transit corridors, and optimize Gasoline (Gas), Diesel, and EV fuel consumption.</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <div class="input-group" style="max-width: 320px;">
            <input type="text" id="routeSearchInput" class="form-control rounded-start-3 border-secondary-subtle" placeholder="Search route, hub, location..." onkeyup="filterRoutesTable()" oninput="filterRoutesTable()">
            <button class="btn btn-danger rounded-end-3 fw-bold" type="button" onclick="filterRoutesTable()" style="background: #CE2029 !important;">
                <i class="bi bi-search me-1"></i> Search
            </button>
        </div>
    </div>
</div>

<!-- Inter-System Integration Connections Badge Banner -->
<div class="alert alert-dark bg-dark text-white border-0 rounded-4 p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-diagram-3-fill text-warning fs-4 me-2"></i>
            <div>
                <span class="fw-bold d-block text-white small">HIRNA MOBILITY INTER-SYSTEM INTEGRATION PIPELINE (RPO)</span>
                <span class="text-white-50 fw-medium" style="font-size: 11px;">Connected to peer enterprise systems for customer fare estimation, multi-fuel eco-routing, and hub transit paths.</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2"><i class="bi bi-geo me-1"></i> Passenger Fare & Route Estimation</span>
            <span class="badge bg-success text-white fw-bold px-3 py-2"><i class="bi bi-buildings me-1"></i> Facilities Hub Transit Paths</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Panel: Interactive Route Planner -->
    <div class="col-lg-5">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-compass-fill text-danger me-2"></i> Hirna Multi-Fuel Route Planner</h5>
            <form id="routePlannerForm" onsubmit="calculateOptimizedRoutes(event);">
                <div class="mb-3">
                    <label class="form-label fw-medium">Origin Hub / Location</label>
                    <select id="routeStart" class="form-select rounded-3" required>
                        @foreach($hubs as $hubName => $coords)
                            <option value="{{ $hubName }}" {{ $loop->first ? 'selected' : '' }}>{{ $hubName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Destination Hub / Location</label>
                    <select id="routeEnd" class="form-select rounded-3" required>
                        @foreach($hubs as $hubName => $coords)
                            <option value="{{ $hubName }}" {{ $loop->iteration == 2 ? 'selected' : '' }}>{{ $hubName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Fuel / Energy Engine</label>
                        <select id="routeFuelType" class="form-select rounded-3" required>
                            <option value="gasoline" selected>⛽ Gasoline (Gas)</option>
                            <option value="diesel">🛢️ Diesel</option>
                            <option value="electric">⚡ Electric (EV)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Vehicle Category</label>
                        <select id="routeVehicleType" class="form-select rounded-3" required>
                            <option value="Sedan" selected>🚕 Hirna Taxi Sedan / Nerio</option>
                            <option value="Hirna Traysikel">🛺 Hirna Traysikel (3-Wheeler)</option>
                            <option value="SUV">🚙 Hirna SUV / MPV</option>
                            <option value="Crossover">🚘 Crossover Fleet</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-3 rounded-3 fw-bold shadow-sm" style="background: #CE2029 !important;">
                    <i class="bi bi-geo-alt-fill me-1"></i> Calculate Optimized Fuel & Eco-Routes
                </button>
            </form>

            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold small text-dark mb-2">Supported Transit Hubs:</h6>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($hubs as $name => $c)
                        <span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1" style="font-size: 11px;">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Interactive Route Map & Optimization Options -->
    <div class="col-lg-7">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-map-fill text-danger me-2"></i> Live OpenStreetMap Route Visualizer</h5>
            
            <div class="card border-0 rounded-4 overflow-hidden shadow-sm mb-3" style="height: 320px; position: relative;">
                <div id="routeVisualizerMap" style="width: 100%; height: 100%; z-index: 1;"></div>
                <div class="position-absolute top-0 end-0 m-2 bg-dark bg-opacity-80 text-white px-3 py-1 rounded-pill small shadow-sm" style="z-index: 10; font-size: 11px; backdrop-filter: blur(4px);">
                    <span class="spinner-grow spinner-grow-sm text-danger me-1" role="status"></span>
                    <span class="fw-bold text-white">HIRNA MULTI-FUEL ROUTING</span>
                </div>
            </div>

            <!-- Route Comparison Options Container -->
            <div id="routeResultsContainer">
                <div class="alert alert-light text-center py-4 border rounded-3 mb-0">
                    <i class="bi bi-compass fs-1 text-danger mb-2 d-block"></i>
                    <h6 class="fw-bold">Ready to Optimize Routes</h6>
                    <p class="small text-muted mb-0">Select origin, destination, and fuel engine type on the left to display optimized route options and fuel/energy predictions.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metro Manila Hub Distance Matrix Table -->
<div class="card premium-card p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-grid-3x3-gap-fill text-info me-2"></i> Metro Manila Hub Distance Matrix</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle text-center" id="distanceMatrixTable">
            <thead>
                <tr class="text-muted" style="font-size: 12px;">
                    <th class="text-start">HUB LOCATION</th>
                    <th>MANILA</th>
                    <th>MAKATI</th>
                    <th>BGC</th>
                    <th>PASAY</th>
                    <th>NAIA</th>
                    <th>QUEZON CITY</th>
                    <th>ORTIGAS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-start fw-bold">Manila Hub</td>
                    <td><span class="badge bg-light text-dark">0 km</span></td>
                    <td>10.5 km</td>
                    <td>12.8 km</td>
                    <td>8.2 km</td>
                    <td>11.0 km</td>
                    <td>9.4 km</td>
                    <td>11.2 km</td>
                </tr>
                <tr>
                    <td class="text-start fw-bold">Makati Hub</td>
                    <td>10.5 km</td>
                    <td><span class="badge bg-light text-dark">0 km</span></td>
                    <td>4.2 km</td>
                    <td>5.8 km</td>
                    <td>7.5 km</td>
                    <td>14.1 km</td>
                    <td>6.3 km</td>
                </tr>
                <tr>
                    <td class="text-start fw-bold">BGC Hub</td>
                    <td>12.8 km</td>
                    <td>4.2 km</td>
                    <td><span class="badge bg-light text-dark">0 km</span></td>
                    <td>7.9 km</td>
                    <td>9.1 km</td>
                    <td>15.0 km</td>
                    <td>5.4 km</td>
                </tr>
                <tr>
                    <td class="text-start fw-bold">Quezon City Hub</td>
                    <td>9.4 km</td>
                    <td>14.1 km</td>
                    <td>15.0 km</td>
                    <td>16.5 km</td>
                    <td>18.2 km</td>
                    <td><span class="badge bg-light text-dark">0 km</span></td>
                    <td>8.8 km</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
function filterRoutesTable() {
    const inputEl = document.getElementById('routeSearchInput');
    if (!inputEl) return;
    const input = inputEl.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#routeResultsContainer .route-option-card, #distanceMatrixTable tbody tr');
    rows.forEach(row => {
        const text = (row.textContent || row.innerText || '').toLowerCase();
        row.style.display = (!input || text.includes(input)) ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('routeSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterRoutesTable();
            }
        });
        searchInput.addEventListener('input', filterRoutesTable);
    }
});
</script>
@endsection
