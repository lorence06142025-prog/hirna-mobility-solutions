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
        <button class="btn btn-outline-success rounded-3" onclick="exportRoutesToCSV();">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </button>
        <button class="btn btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#importRoutesCsvModal">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
        </button>
        <button class="btn btn-outline-dark rounded-3" onclick="window.print();">
            <i class="bi bi-printer me-1"></i> Print / PDF Report
        </button>
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

<!-- Import Routes CSV Modal -->
<div class="modal fade" id="importRoutesCsvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog rounded-4 overflow-hidden">
        <div class="modal-content border-0">
            <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module_type" value="trips">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Route Plans (CSV)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Select CSV File (.csv)</label>
                        <input type="file" name="csv_file" accept=".csv, .txt" class="form-control rounded-3" required>
                        <small class="text-muted mt-1 d-block">Expected columns: Origin Hub, Destination Hub, Distance (km), Estimated kWh, ETA (min).</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-cloud-upload me-1"></i> Import Route CSV</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const hubCoords = {
        'Manila': [14.5995, 120.9842],
        'Manila Hub': [14.5995, 120.9842],
        'Manila Hub (Port Area)': [14.5995, 120.9842],
        
        'Makati': [14.5547, 121.0244],
        'Makati Hub': [14.5547, 121.0244],
        'Makati Hub (Ayala Ave)': [14.5547, 121.0244],
        
        'BGC': [14.5492, 121.0558],
        'BGC Hub': [14.5492, 121.0558],
        'BGC Hub (Market Market)': [14.5492, 121.0558],
        'Taguig': [14.5492, 121.0558],

        'Quezon City': [14.6760, 121.0437],
        'Quezon City Hub': [14.6760, 121.0437],
        'Quezon City Hub (Cubao)': [14.6760, 121.0437],

        'Pasay': [14.5378, 120.9993],
        'Pasay Hub': [14.5378, 120.9993],
        'Pasay Hub (MOA Complex)': [14.5378, 120.9993],

        'NAIA': [14.5204, 121.0134],
        'NAIA Hub': [14.5204, 121.0134],
        'NAIA Terminal 3 Hub': [14.5204, 121.0134],

        'Alabang': [14.4172, 121.0408],
        'Alabang Hub': [14.4172, 121.0408],
        'Alabang Hub (Filinvest)': [14.4172, 121.0408],

        'Ortigas': [14.5869, 121.0614],
        'Ortigas Hub': [14.5869, 121.0614],
        'Ortigas Hub (Ortigas Center)': [14.5869, 121.0614]
    };

    let map = null;
    let startMarker = null;
    let endMarker = null;
    let polyline = null;
    let currentRoutesData = [];

    document.addEventListener("DOMContentLoaded", function() {
        map = L.map('routeVisualizerMap', { zoomControl: true }).setView([14.5995, 120.9842], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap &bull; Hirna Mobility Routing'
        }).addTo(map);

        // Auto calculate on load
        calculateOptimizedRoutes();

        // Listen for location dropdown changes to auto-update map & options
        const startElem = document.getElementById('routeStart');
        const endElem = document.getElementById('routeEnd');
        const typeElem = document.getElementById('routeVehicleType');

        if (startElem) startElem.addEventListener('change', calculateOptimizedRoutes);
        if (endElem) endElem.addEventListener('change', calculateOptimizedRoutes);
        if (typeElem) typeElem.addEventListener('change', calculateOptimizedRoutes);
    });

    function getLatLng(name) {
        if (!name) return [14.5995, 120.9842];
        if (hubCoords[name]) return hubCoords[name];
        for (let key in hubCoords) {
            if (name.toLowerCase().includes(key.toLowerCase()) || key.toLowerCase().includes(name.toLowerCase())) {
                return hubCoords[key];
            }
        }
        return [14.5995, 120.9842];
    }

    function calculateOptimizedRoutes(e) {
        if (e && e.preventDefault) e.preventDefault();
        const start = document.getElementById('routeStart').value;
        const end = document.getElementById('routeEnd').value;
        const type = document.getElementById('routeVehicleType').value;
        const fuelType = document.getElementById('routeFuelType') ? document.getElementById('routeFuelType').value : 'gasoline';

        if (!start || !end) return;
        if (start === end) {
            alert('Please select different Origin and Destination hubs!');
            return;
        }

        fetch("{{ route('routes.plan') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ start, end, vehicle_type: type, fuel_type: fuelType })
        })
        .then(res => res.json())
        .then(data => {
            currentRoutesData = data.routes;
            renderRouteOptionsList(data.routes);
            renderRouteVisualizer(start, end, 0);
        })
        .catch(err => console.error(err));
    }

    function renderRouteVisualizer(startName, endName, routeIdx = 0) {
        const startLatLng = getLatLng(startName);
        const endLatLng = getLatLng(endName);

        const routeColors = ['#10B981', '#0284C7', '#F59E0B'];
        const strokeColor = routeColors[routeIdx] || '#10B981';

        if (startMarker) map.removeLayer(startMarker);
        if (endMarker) map.removeLayer(endMarker);
        if (polyline) map.removeLayer(polyline);

        startMarker = L.circleMarker(startLatLng, { color: '#10B981', radius: 10, fillColor: '#10B981', fillOpacity: 0.95 }).addTo(map).bindPopup("<b>Origin Hub:</b> " + startName);
        endMarker = L.circleMarker(endLatLng, { color: '#EF4444', radius: 10, fillColor: '#EF4444', fillOpacity: 0.95 }).addTo(map).bindPopup("<b>Destination:</b> " + endName);

        // Fetch real OSRM OpenStreetMap turn-by-turn road geometry (follows real streets, turns, & highways)
        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${startLatLng[1]},${startLatLng[0]};${endLatLng[1]},${endLatLng[0]}?overview=full&geometries=geojson`;

        fetch(osrmUrl)
            .then(res => res.json())
            .then(osrmData => {
                if (osrmData.routes && osrmData.routes.length > 0) {
                    const routeCoordinates = osrmData.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    polyline = L.polyline(routeCoordinates, { color: strokeColor, weight: 6, opacity: 0.9 }).addTo(map);
                    map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
                } else {
                    polyline = L.polyline([startLatLng, endLatLng], { color: strokeColor, weight: 6, opacity: 0.9, dashArray: '6, 6' }).addTo(map);
                    map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
                }
            })
            .catch(() => {
                polyline = L.polyline([startLatLng, endLatLng], { color: strokeColor, weight: 6, opacity: 0.9, dashArray: '6, 6' }).addTo(map);
                map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
            });
    }

    function selectRouteOption(idx) {
        const start = document.getElementById('routeStart').value;
        const end = document.getElementById('routeEnd').value;

        document.querySelectorAll('.route-option-card').forEach((card, i) => {
            if (i === idx) {
                card.classList.add('border-primary', 'shadow-sm');
                card.classList.remove('border-secondary-subtle');
            } else {
                card.classList.remove('border-primary', 'shadow-sm');
            }
        });

        renderRouteVisualizer(start, end, idx);
    }

    function renderRouteOptionsList(routes) {
        const container = document.getElementById('routeResultsContainer');
        container.innerHTML = `<h6 class="fw-bold mb-3 text-dark">Optimized Eco-Route Comparison Options (Click any route to view on map):</h6>`;

        routes.forEach((r, idx) => {
            const isBest = idx === 0;
            const borderStyle = isBest ? 'border-success bg-success bg-opacity-10' : 'bg-light';
            const unitLabel = r.fuel_unit || 'Liters (Gas)';
            const fuelVal = r.estimated_fuel || r.predicted_kwh || 0;
            container.innerHTML += `
                <div class="card border rounded-3 p-3 mb-3 route-option-card ${borderStyle}" onclick="selectRouteOption(${idx});" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark fs-6">${r.name}</span>
                        <span class="badge ${isBest ? 'bg-success text-white' : 'bg-secondary text-white'} px-3 py-1 rounded-pill">${r.tag || 'Eco-Path'}</span>
                    </div>
                    <div class="row text-center my-2 g-2">
                        <div class="col-4">
                            <small class="text-muted d-block" style="font-size: 11px;">DISTANCE</small>
                            <strong class="text-dark">${r.distance_km} km</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block" style="font-size: 11px;">ESTIMATED ETA</small>
                            <strong class="text-dark">${r.duration_minutes} mins</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block" style="font-size: 11px;">ESTIMATED CONSUMPTION</small>
                            <strong class="text-success">${fuelVal} ${unitLabel} (₱${r.charging_cost_php})</strong>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <small class="text-muted" style="font-size: 12px;"><i class="bi bi-info-circle me-1"></i> ${r.description}</small>
                        <span class="badge bg-white text-dark border px-2 py-1 small">${r.traffic_condition || 'Normal Flow'}</span>
                    </div>
                </div>
            `;
        });
    }


    function exportRoutesToCSV() {
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
        downloadLink.download = "Hirna_Route_Planning_Matrix.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
</script>
@endsection
