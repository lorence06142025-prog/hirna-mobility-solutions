@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="page-header-title">Fuel & EV Energy Management System</h2>
        <p class="page-header-subtitle">Log EV battery charging, monitor kWh energy metrics, and test AI prediction models.</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <button class="btn btn-outline-success rounded-3" onclick="exportFuelTableToCSV();">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </button>
        <button class="btn btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#importFuelCsvModal">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
        </button>
        <button class="btn btn-outline-dark rounded-3" onclick="window.print();">
            <i class="bi bi-printer me-1"></i> Print / PDF
        <button class="btn btn-premium d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#logFuelModal">
            <i class="bi bi-plus-circle me-1"></i> Log Refuel / EV Charging
        </button>
    </div>
</div>


<!-- Inter-System Integration Connections Badge Banner -->
<div class="alert alert-dark bg-dark text-white border-0 rounded-4 p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-diagram-3-fill text-success fs-4 me-2"></i>
            <div>
                <span class="fw-bold d-block text-white small">INTER-SYSTEM INTEGRATION PIPELINE (TEAM 7 &bull; FMS)</span>
                <span class="text-white fw-medium" style="font-size: 11px;">Connected to peer enterprise systems for EV charging expense exports, station bay reservations, and driver energy logs.</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-info text-dark fw-bold px-3 py-2"><i class="bi bi-cash-coin me-1"></i> Team 5: Financials Charging AP/GL</span>
            <span class="badge bg-success text-white fw-bold px-3 py-2"><i class="bi bi-ev-station me-1"></i> Team 8: Facilities Charging Bays</span>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2"><i class="bi bi-person-badge me-1"></i> Team 9: Ops Driver Energy Logs</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Weights Info -->
    <div class="col-md-4">
        <div class="card premium-card p-4 h-100 bg-secondary text-white border-0">
            <h5 class="fw-bold mb-3 text-info"><i class="bi bi-cpu-fill me-1"></i> Active AI Model Weights</h5>
            <p class="text-white-50" style="font-size: 13px;">
                Coefficients trained by Gradient Descent regression engine on historical Hirna Vehicle trip logs.
            </p>
            <div class="mt-3">
                <div class="d-flex justify-content-between border-bottom border-dark py-2">
                    <span class="text-white-50">Intercept (&beta;<sub>0</sub>)</span>
                    <span class="fw-bold text-info">{{ number_format($weights['intercept'] ?? 0.5, 4) }}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom border-dark py-2">
                    <span class="text-white-50">Distance Cost (&beta;<sub>1</sub> per km)</span>
                    <span class="fw-bold text-info">{{ number_format($weights['distance'] ?? 0.12, 4) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 mb-3">
                    <span class="text-white-50">Speed Penalty (&beta;<sub>2</sub> per km/h)</span>
                    <span class="fw-bold text-info">{{ number_format($weights['speed'] ?? 0.0005, 5) }}</span>
                </div>
            </div>

            <form action="{{ route('fuel.train') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-info w-100 rounded-3 text-dark fw-bold">
                    <i class="bi bi-gear-fill me-1"></i> Re-Train AI EV Model
                </button>
            </form>
        </div>
    </div>

    <!-- AI Energy Consumption Test Simulator Panel -->
    <div class="col-md-8">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-calculator text-primary me-2"></i> AI EV Energy Predictor Test Panel</h5>
            
            <div class="row">
                <!-- Form Inputs -->
                <div class="col-md-6 border-end">
                    <form id="aiPredictForm" onsubmit="runPredictionTest(event);">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-weight: 500;">Planned Distance (km)</label>
                                <input type="number" step="0.1" id="testDistance" class="form-control rounded-3" placeholder="e.g. 15.2" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-weight: 500;">Avg Speed (km/h)</label>
                                <input type="number" id="testSpeed" class="form-control rounded-3" placeholder="e.g. 45" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-weight: 500;">Fuel / Energy Engine</label>
                                <select id="testFuelType" class="form-select rounded-3" onchange="updateDefaultFuelPrice();" required>
                                    <option value="gasoline" selected>⛽ Gasoline (Gas)</option>
                                    <option value="diesel">🛢️ Diesel</option>
                                    <option value="electric">⚡ Electric (EV)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-weight: 500;">Fuel Price (₱ / Unit)</label>
                                <input type="number" step="0.1" id="testUnitPrice" class="form-control rounded-3" value="64.50" placeholder="64.50" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500;">Vehicle Category</label>
                            <select id="testVehicleType" class="form-select rounded-3" required>
                                <option value="Sedan" selected>🚕 Hirna Taxi Sedan / Nerio</option>
                                <option value="Hirna Traysikel">🛺 Hirna Traysikel / Tricycle (3-Wheeler)</option>
                                <option value="SUV">🚙 Hirna SUV / MPV Fleet</option>
                                <option value="Crossover">🚘 Crossover Fleet</option>
                                <option value="Hatchback">🚗 Compact / Hatchback</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 500;">Actual Fuel Used (Optional)</label>
                            <input type="number" step="0.1" id="testActualFuel" class="form-control rounded-3" placeholder="e.g. 1.8">
                        </div>
                        <button type="submit" class="btn btn-premium w-100 rounded-3">
                            <i class="bi bi-cpu me-1"></i> Predict Fuel & Price Output
                        </button>
                    </form>
                </div>

                <!-- Live Results Output -->
                <div class="col-md-6 bg-light bg-opacity-50 p-3 rounded-4 d-flex align-items-center justify-content-center" id="predictResultContainer">
                    <div class="text-center text-muted" id="placeholderResult">
                        <i class="bi bi-fuel-pump-fill fs-1 mb-2 text-danger"></i>
                        <h6>Fuel & Cost Estimation Results</h6>
                        <p class="mb-0 small">Select fuel engine type (Gas/Diesel/EV) and trip parameters to predict cost.</p>
                    </div>

                    <div id="actualResultPanel" class="w-100 d-none">
                        <div class="mb-3 text-center">
                            <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;" id="resultFuelUnitHeader">Estimated Fuel Usage</span>
                            <h2 class="fw-bold text-danger mt-1 mb-0" id="resultPredictedLiters">0.00 Liters (Gas)</h2>
                            <small class="text-muted">Total Predicted Trip Cost: <strong class="text-dark fs-6" id="resultCost">₱0.00</strong></small>
                        </div>
                        
                        <div class="border-top pt-2">
                            <h6 class="fw-bold small text-dark mb-1">AI Fuel & Cost Insights:</h6>
                            <ul class="ps-3 mb-2 small text-muted" id="resultInsightsList"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: Enterprise EV Energy Analytics (Simplified Language) -->
<div class="row g-4 mb-4">
    <!-- Peak vs Off-Peak Electricity Tariff Analyzer -->
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-2">
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark small">Electricity Rates & Savings</span>
                    <small class="text-muted" style="font-size: 10px;">Cheapest Overnight Charging</small>
                </div>
            </div>
            <div class="my-2 border-top border-bottom py-2">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Daytime Rate (Peak)</span>
                    <strong class="text-danger">₱13.20 / kWh</strong>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Overnight Rate (Off-Peak)</span>
                    <strong class="text-success">₱8.50 / kWh</strong>
                </div>
            </div>
            <div class="mt-1">
                <small class="text-muted d-block" style="font-size: 11px;">ESTIMATED MONTHLY SAVINGS</small>
                <strong class="text-success fs-5">₱18,450.00</strong>
                <small class="text-success d-block" style="font-size: 10px;"><i class="bi bi-arrow-down-right-circle me-1"></i> 35.6% Saved by Charging at Night</small>
            </div>
        </div>
    </div>

    <!-- Depot Load Balancing & Power Allocation -->
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3 me-2">
                    <i class="bi bi-lightning-charge fs-5"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark small">Charging Station Power</span>
                    <small class="text-muted" style="font-size: 10px;">Active Charging Slots</small>
                </div>
            </div>
            <div class="my-2">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Electricity Currently Used</span>
                    <strong class="text-dark">142 kW / 200 kW</strong>
                </div>
                <div class="progress rounded-pill mb-2" style="height: 8px;">
                    <div class="progress-bar bg-warning" style="width: 71%;"></div>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                    <span>Slow Chargers: 22 kW</span>
                    <span>Fast Chargers: 120 kW</span>
                </div>
            </div>
            <span class="badge bg-success text-white mt-1 small"><i class="bi bi-shield-check me-1"></i> Safe Power Level Maintained</span>
        </div>
    </div>

    <!-- Battery Degradation & State of Health Forecaster -->
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 me-2">
                    <i class="bi bi-battery-charging fs-5"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark small">Battery Health & Lifespan</span>
                    <small class="text-muted" style="font-size: 10px;">Condition Tracker</small>
                </div>
            </div>
            <div class="my-2 border-top border-bottom py-2">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Current Battery Health</span>
                    <strong class="text-success">98.4% Health</strong>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Total Times Charged</span>
                    <strong class="text-dark">342 / 1,500 Cycles</strong>
                </div>
            </div>
            <div class="mt-1">
                <small class="text-muted d-block" style="font-size: 11px;">ESTIMATED BATTERY LIFE LEFT</small>
                <strong class="text-primary fs-6">6.8 Years (185,000 km)</strong>
            </div>
        </div>
    </div>

    <!-- ESG Carbon Avoidance & Sustainability Scorecard -->
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-info bg-opacity-10 text-info p-2 rounded-3 me-2">
                    <i class="bi bi-tree fs-5 text-success"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark small">Clean Air & Eco Savings</span>
                    <small class="text-muted" style="font-size: 10px;">Pollution Reduced</small>
                </div>
            </div>
            <div class="my-2">
                <small class="text-muted d-block" style="font-size: 11px;">GAS SMOKE & CO₂ SAVED</small>
                <h3 class="fw-bold text-success my-1">12.4 <small class="fs-6 text-dark">Tons CO₂</small></h3>
                <small class="text-muted" style="font-size: 11px;">Compared to gas-powered vehicles</small>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-30 small mt-1">
                <i class="bi bi-tree-fill me-1"></i> Equal to Planting 540 Trees
            </span>
        </div>
    </div>
</div>

<!-- Recent Charging & Energy Logs Table with Pagination -->
<div class="card premium-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0"><i class="bi bi-fuel-pump-fill text-danger me-2"></i> Hirna Fleet Refueling & EV Charging Logs</h5>
        <span class="badge bg-danger text-white rounded-pill px-3 py-2" style="background: #CE2029 !important;">Rates: Gas ₱64.50/L &bull; Diesel ₱58.00/L &bull; EV ₱11.50/kWh</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="fuelLogsTable">
            <thead>
                <tr class="text-muted" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                    <th>DATE</th>
                    <th>HIRNA FLEET VEHICLE</th>
                    <th>DRIVER / OPERATOR</th>
                    <th>FUEL / ENERGY ADDED</th>
                    <th>REFUEL / CHARGE COST (₱)</th>
                    <th>ODOMETER (KM)</th>
                    <th>REFUELING / CHARGING TYPE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="font-size: 13px;">{{ \Carbon\Carbon::parse($log->date)->toFormattedDateString() }}</td>
                        <td>
                            <strong class="text-dark d-block" style="font-size: 14px;">{{ $log->vehicle->make }} {{ $log->vehicle->model }}</strong>
                            <small class="badge bg-dark" style="font-size: 10px;">{{ $log->vehicle->license_plate }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 text-danger fw-bold rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-size:12px;">
                                    {{ strtoupper(substr($log->trip->driver->user->name ?? 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <strong class="d-block text-dark small">{{ $log->trip->driver->user->name ?? 'Juan Dela Cruz' }}</strong>
                                    <small class="text-muted" style="font-size: 11px;">License: {{ $log->trip->driver->license_number ?? 'N01-18-99201' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="fw-bold text-dark" style="font-size: 14px;">
                            @php
                                $isEvLog = str_contains(strtolower($log->fuel_type ?? ''), 'electric') || str_contains(strtolower($log->fuel_type ?? ''), 'kwh') || str_contains(strtolower($log->vehicle->model ?? ''), 'vinfast') || str_contains(strtolower($log->vehicle->type ?? ''), 'ev');
                            @endphp
                            @if(str_contains(strtolower($log->fuel_type ?? ''), 'diesel'))
                                <span class="text-warning"><i class="bi bi-fuel-pump-fill me-1"></i> {{ number_format($log->amount_liters, 2) }} Liters (Diesel)</span>
                            @elseif($isEvLog)
                                <span class="text-success"><i class="bi bi-lightning-charge me-1"></i> {{ number_format($log->amount_liters, 2) }} kWh (EV)</span>
                            @else
                                <span class="text-danger"><i class="bi bi-fuel-pump me-1"></i> {{ number_format($log->amount_liters, 2) }} Liters (Gas)</span>
                            @endif
                        </td>
                        <td class="fw-bold text-dark" style="font-size: 14px;">₱{{ number_format($log->cost, 2) }}</td>
                        <td style="font-size: 13px;">{{ number_format($log->odometer_reading, 1) }} km</td>
                        <td>
                            <span class="badge bg-dark text-white px-3 py-2 rounded-3 shadow-sm fw-bold">
                                {{ $log->fuel_type ?: ($isEvLog ? 'Electric Charging (Hirna Depot)' : 'Gasoline Refueling (Hirna Station)') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No refueling or charging logs recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls (Page 1, Page 2, Next, Prev) -->
    @if(method_exists($logs, 'hasPages') && $logs->hasPages())
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top mt-3">
            <div class="small text-muted">
                Showing <span class="fw-bold text-dark">{{ $logs->firstItem() }}</span> to <span class="fw-bold text-dark">{{ $logs->lastItem() }}</span> of <span class="fw-bold text-dark">{{ $logs->total() }}</span> total fuel logs
            </div>
            <div>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- Modal Log Refill -->
<div class="modal fade" id="logFuelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog rounded-4 overflow-hidden">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('fuel.store') }}" method="POST">
                @csrf
                <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #CE2029 0%, #7F1D1D 100%);">
                    <h5 class="modal-title fw-bold"><i class="bi bi-fuel-pump-fill me-2"></i> Log Refueling / EV Charging Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Select Hirna Fleet Vehicle</label>
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

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Driver / Operator</label>
                        <select name="driver_id" class="form-select rounded-3">
                            @foreach($drivers as $drv)
                                <option value="{{ $drv->id }}">{{ $drv->user->name ?? 'Driver' }} (License: {{ $drv->license_number }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Transaction Date</label>
                        <input type="date" name="date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-weight: 500;">Quantity (Liters or kWh)</label>
                            <input type="number" step="0.01" name="amount_liters" class="form-control rounded-3" placeholder="e.g. 25.5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-weight: 500;">Total Cost (₱)</label>
                            <input type="number" step="0.01" name="cost" class="form-control rounded-3" placeholder="e.g. 1644.75" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Odometer Reading (km)</label>
                        <input type="number" step="0.1" name="odometer_reading" class="form-control rounded-3" placeholder="e.g. 12580" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Fuel / Energy Station Type</label>
                        <select name="fuel_type" class="form-select rounded-3" required>
                            <option value="Gasoline Refueling" selected>⛽ Gasoline Refueling (Hirna Station)</option>
                            <option value="Diesel Refueling">🛢️ Diesel Refueling (Hirna Station)</option>
                            <option value="Electric (kWh)">⚡ Fast DC EV Charging (Hirna Depot)</option>
                            <option value="AC Level 2 Depot Charging">🔌 AC Level 2 Depot Charging</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="submit" class="btn btn-success w-100 rounded-3">
                        <i class="bi bi-save me-1"></i> Save Charging Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateDefaultFuelPrice() {
    const fuelType = document.getElementById('testFuelType').value;
    const priceInput = document.getElementById('testUnitPrice');
    if (!priceInput) return;
    
    if (fuelType === 'gasoline') {
        priceInput.value = "64.50";
    } else if (fuelType === 'diesel') {
        priceInput.value = "58.00";
    } else if (fuelType === 'electric') {
        priceInput.value = "11.50";
    }
}

function runPredictionTest(e) {
    e.preventDefault();
    const distance = document.getElementById('testDistance').value;
    const speed = document.getElementById('testSpeed').value;
    const type = document.getElementById('testVehicleType').value;
    const fuelType = document.getElementById('testFuelType').value;
    const unitPrice = document.getElementById('testUnitPrice').value;
    const actualFuel = document.getElementById('testActualFuel').value;

    fetch("{{ route('fuel.predict') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ distance, speed, vehicle_type: type, fuel_type: fuelType, unit_price: unitPrice, actual_fuel: actualFuel })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('placeholderResult').classList.add('d-none');
        document.getElementById('actualResultPanel').classList.remove('d-none');

        const quantity = data.predicted_fuel;
        const fuelUnit = data.fuel_unit || 'Liters';
        const cost = data.predicted_cost || (quantity * (unitPrice || 64.50));

        document.getElementById('resultPredictedLiters').innerText = quantity.toFixed(2) + " " + fuelUnit;
        document.getElementById('resultCost').innerText = "₱" + Number(cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " (@ ₱" + parseFloat(unitPrice || 64.50).toFixed(2) + ")";

        const list = document.getElementById('resultInsightsList');
        list.innerHTML = "";
        data.insights.forEach(insight => {
            list.innerHTML += `<li>${insight}</li>`;
        });
    })
    .catch(err => console.error(err));
}

function exportFuelTableToCSV() {
    let csv = [];
    const rows = document.querySelectorAll("#fuelLogsTable tr");
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length; j++) 
            row.push('"' + cols[j].innerText.replace(/"/g, '""').trim() + '"');
        csv.push(row.join(","));
    }

    const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    const downloadLink = document.createElement("a");
    downloadLink.download = "Hirna_Fuel_and_Energy_Logs.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

<!-- Import EV Fuel Logs CSV Modal -->
<div class="modal fade" id="importFuelCsvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog rounded-4 overflow-hidden">
        <div class="modal-content border-0">
            <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module_type" value="fuel">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import EV Charging Logs (CSV)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Select CSV File (.csv)</label>
                        <input type="file" name="csv_file" accept=".csv, .txt" class="form-control rounded-3" required>
                        <small class="text-muted mt-1 d-block">Expected columns: Date, Vehicle Plate, Energy (kWh), Cost (₱), Odometer (km), Charging Type.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-3"><i class="bi bi-cloud-upload me-1"></i> Import Charging CSV</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
