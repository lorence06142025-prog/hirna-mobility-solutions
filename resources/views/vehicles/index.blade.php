@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="page-header-title">Fleet and Vehicle Management</h2>
        <p class="page-header-subtitle">Manage Hirna Mobility Fleet vehicle inventory, battery storage, and active fleet statuses.</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap align-items-center">
        <!-- Search Bar -->
        <div class="input-group" style="max-width: 250px;">
            <input type="text" id="vehicleSearchInput" class="form-control rounded-start-3 border-secondary-subtle" placeholder="Search plate, model..." onkeyup="filterVehiclesTable()">
            <button class="btn btn-danger rounded-end-3 fw-bold" type="button" onclick="filterVehiclesTable()" style="background: #CE2029 !important;">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <!-- Filter Dropdown (Refine by Vehicle/Fuel Type) -->
        <select id="vehicleTypeFilter" class="form-select rounded-3 border-secondary-subtle" style="width: 170px;" onchange="filterVehiclesTable()">
            <option value="">All Vehicle Types</option>
            <option value="taxi">Taxi Sedan</option>
            <option value="mpv">MPV / SUV</option>
            <option value="van">Shuttle Van</option>
            <option value="electric">Electric (EV)</option>
            <option value="traysikel">Hirna Traysikel</option>
        </select>

        <!-- Section-Level Export Options -->
        <button class="btn btn-outline-success rounded-3 fw-bold shadow-sm" onclick="exportVehiclesToCSV();">
            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
        </button>
        <button class="btn btn-outline-dark rounded-3 fw-bold shadow-sm" onclick="window.print();">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
        </button>

        <button class="btn btn-premium d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="bi bi-plus-circle me-1"></i> Register Hirna Vehicle
        </button>
    </div>
</div>

<!-- Inter-System Integration Connections Badge Banner -->
<div class="alert alert-dark bg-dark text-white border-0 rounded-4 p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-diagram-3-fill text-success fs-4 me-2"></i>
            <div>
                <span class="fw-bold d-block text-white small">INTER-SYSTEM INTEGRATION PIPELINE (TEAM 7 &bull; FVM)</span>
                <span class="text-white fw-medium" style="font-size: 11px;">Connected to peer enterprise systems for driver registration, procurement, and facility compliance.</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-info text-dark fw-bold px-3 py-2"><i class="bi bi-person-plus me-1"></i> Team 1: HRMS Driver Roster</span>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2"><i class="bi bi-cart-check me-1"></i> Team 6: Supply Chain Parts PR</span>
            <span class="badge bg-success text-white fw-bold px-3 py-2"><i class="bi bi-building me-1"></i> Team 8: Facilities Contracts</span>
        </div>
    </div>
</div>

<!-- Registry Tabs -->
<div class="card premium-card p-4">
    <ul class="nav nav-tabs border-bottom mb-4" id="registryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold text-dark border-0 border-bottom border-primary" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles-pane" type="button" role="tab" aria-controls="vehicles-pane" aria-selected="true">
                <i class="bi bi-truck me-1"></i> Hirna Vehicle Units ({{ $vehicles->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-muted border-0" id="pms-tab" data-bs-toggle="tab" data-bs-target="#pms-pane" type="button" role="tab" aria-controls="pms-pane" aria-selected="false">
                <i class="bi bi-wrench-adjustable me-1"></i> Preventive Maintenance Services (PMS)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-muted border-0" id="battery-tab" data-bs-toggle="tab" data-bs-target="#battery-pane" type="button" role="tab" aria-controls="battery-pane" aria-selected="false">
                <i class="bi bi-battery-charging me-1"></i> EV Battery Health & Telematics
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-muted border-0" id="drivers-tab" data-bs-toggle="tab" data-bs-target="#drivers-pane" type="button" role="tab" aria-controls="drivers-pane" aria-selected="false">
                <i class="bi bi-people me-1"></i> Driver Directory (Synced from Team 9)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="registryTabsContent">
        <!-- Vehicles List Tab -->
        <div class="tab-pane fade show active" id="vehicles-pane" role="tabpanel" aria-labelledby="vehicles-tab" tabindex="0">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="vehiclesTable">
                    <thead>
                        <tr class="text-muted" style="font-size: 13px;">
                            <th>HIRNA EV MODEL</th>
                            <th>PLATE NUMBER</th>
                            <th>YEAR</th>
                            <th>BATTERY CAPACITY</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $vehicle)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 p-2 rounded-3 me-2">
                                            <i class="bi bi-ev-front-fill text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block text-dark">{{ $vehicle->make }} {{ $vehicle->model }}</span>
                                            <small class="badge bg-secondary" style="font-size: 10px;">{{ $vehicle->type }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold text-dark">{{ $vehicle->license_plate }}</td>
                                <td>{{ $vehicle->year }}</td>
                                <td class="fw-bold text-success">
                                    <i class="bi bi-battery-charging me-1"></i> {{ $vehicle->fuel_capacity }} kWh
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $vehicle->status === 'active' ? 'bg-success' : ($vehicle->status === 'maintenance' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ ucfirst($vehicle->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if($vehicle->status === 'maintenance')
                                            <form action="{{ route('vehicles.toggle-status', $vehicle) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm btn-success rounded-2 px-2 shadow-sm fw-bold" style="font-size: 11px;" title="Complete servicing and release vehicle back to Active Available status">
                                                    ✅ Release to Active
                                                </button>
                                            </form>
                                        @elseif($vehicle->status === 'active')
                                            <form action="{{ route('vehicles.toggle-status', $vehicle) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="maintenance">
                                                <button type="submit" class="btn btn-sm btn-warning text-dark rounded-2 px-2 shadow-sm fw-bold" style="font-size: 11px;" title="Set vehicle status to Maintenance (Not Available)">
                                                    ⚙️ Send to Maintenance
                                                </button>
                                            </form>
                                        @endif
                                        <button class="btn btn-sm btn-primary rounded-2 px-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#editVehicleModal{{ $vehicle->id }}" title="Edit Vehicle">
                                            <i class="bi bi-pencil-fill me-1"></i> Edit
                                        </button>
                                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 px-2 shadow-sm" title="Delete Vehicle">
                                                <i class="bi bi-trash-fill me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Vehicle Modal -->
                            <div class="modal fade" id="editVehicleModal{{ $vehicle->id }}" tabindex="-1" aria-labelledby="editVehicleModalLabel{{ $vehicle->id }}" aria-hidden="true">
                                <div class="modal-dialog rounded-4 overflow-hidden">
                                    <div class="modal-content border-0">
                                        <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header bg-secondary text-white border-0">
                                                <h5 class="modal-title fw-bold" id="editVehicleModalLabel{{ $vehicle->id }}">Edit Hirna Vehicle Details</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label" style="font-weight: 500;">Make (Brand)</label>
                                                        <input type="text" name="make" value="{{ $vehicle->make }}" class="form-control rounded-3" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" style="font-weight: 500;">Model (Name)</label>
                                                        <input type="text" name="model" value="{{ $vehicle->model }}" class="form-control rounded-3" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" style="font-weight: 500;">License Plate</label>
                                                        <input type="text" name="license_plate" value="{{ $vehicle->license_plate }}" class="form-control rounded-3" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" style="font-weight: 500;">Year</label>
                                                        <input type="number" name="year" value="{{ $vehicle->year }}" class="form-control rounded-3" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" style="font-weight: 500;">Vehicle Category</label>
                                                        <select name="type" class="form-select rounded-3" required>
                                                            <option value="Sedan" {{ $vehicle->type === 'Sedan' ? 'selected' : '' }}>Sedan (Nerio Green)</option>
                                                            <option value="SUV" {{ $vehicle->type === 'SUV' ? 'selected' : '' }}>SUV (VF 8 / VF 9)</option>
                                                            <option value="Crossover" {{ $vehicle->type === 'Crossover' ? 'selected' : '' }}>Crossover (VF e34)</option>
                                                            <option value="Hatchback" {{ $vehicle->type === 'Hatchback' ? 'selected' : '' }}>Compact (VF 5)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" style="font-weight: 500;">Battery Storage (kWh)</label>
                                                        <input type="number" name="fuel_capacity" value="{{ $vehicle->fuel_capacity }}" step="0.1" class="form-control rounded-3" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label" style="font-weight: 500;">Status</label>
                                                        <select name="status" class="form-select rounded-3" required>
                                                            <option value="active" {{ $vehicle->status === 'active' ? 'selected' : '' }}>Active (Available)</option>
                                                            <option value="maintenance" {{ $vehicle->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                            <option value="offline" {{ $vehicle->status === 'offline' ? 'selected' : '' }}>Offline</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 p-3 bg-light">
                                                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-premium rounded-3">Update Vehicle</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No fleet vehicles registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PMS Maintenance Tab -->
        <div class="tab-pane fade" id="pms-pane" role="tabpanel" aria-labelledby="pms-tab" tabindex="0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-wrench-adjustable text-primary me-2"></i> Vehicle Maintenance & Tune-up History</h6>
                <button class="btn btn-sm btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#schedulePMSModal" onclick="openScheduleModal();">
                    <i class="bi bi-plus-circle me-1"></i> Schedule Maintenance Service
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted" style="font-size: 13px;">
                            <th>HIRNA FLEET VEHICLE</th>
                            <th>SERVICE TYPE</th>
                            <th>SCHEDULED DATE</th>
                            <th>COST (PHP)</th>
                            <th>SERVICING STATUS</th>
                            <th>SUPPLY CHAIN PR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenanceRecords as $pms)
                            <tr>
                                <td>
                                    <strong class="d-block text-dark small">{{ $pms->vehicle->make ?? 'Hirna' }} {{ $pms->vehicle->model ?? 'EV' }}</strong>
                                    <small class="text-muted" style="font-size: 11px;">Plate: {{ $pms->vehicle->license_plate ?? 'N/A' }}</small>
                                </td>
                                <td><span class="badge bg-secondary">{{ $pms->service_type }}</span></td>
                                <td>{{ $pms->scheduled_date }}</td>
                                <td class="fw-bold text-dark">₱{{ number_format($pms->cost ?? 1250, 2) }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $pms->status === 'completed' ? 'bg-success' : ($pms->status === 'scheduled' ? 'bg-warning text-dark' : 'bg-primary') }}">
                                        {{ ucfirst($pms->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark" style="font-size: 11px;"><i class="bi bi-cart-check me-1"></i> Team 6 PR Issued</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td><strong class="d-block text-dark small">Hyundai Accent Hirna Taxi (TXI-9876)</strong></td>
                                <td><span class="badge bg-secondary">Brake Pad & Battery Diagnostic</span></td>
                                <td>2026-08-20</td>
                                <td class="fw-bold text-dark">₱3,450.00</td>
                                <td><span class="badge rounded-pill bg-warning text-dark">Scheduled</span></td>
                                <td><span class="badge bg-info text-dark" style="font-size: 11px;"><i class="bi bi-cart-check me-1"></i> Team 6 PR Issued</span></td>
                            </tr>
                            <tr>
                                <td><strong class="d-block text-dark small">Toyota Vios Hirna Taxi (TXI-5421)</strong></td>
                                <td><span class="badge bg-secondary">Tire Rotation & Coolant Flushing</span></td>
                                <td>2026-08-14</td>
                                <td class="fw-bold text-dark">₱1,800.00</td>
                                <td><span class="badge rounded-pill bg-success">Completed</span></td>
                                <td><span class="badge bg-info text-dark" style="font-size: 11px;"><i class="bi bi-cart-check me-1"></i> Team 6 PR Issued</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- EV Battery Health Tab -->
        <div class="tab-pane fade" id="battery-pane" role="tabpanel" aria-labelledby="battery-tab" tabindex="0">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-battery-charging text-success me-2"></i> Battery Telematics & State-of-Health (SoH) Diagnostics</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border rounded-3 p-3 bg-light text-center">
                        <small class="text-muted d-block" style="font-size: 11px;">AVG BATTERY HEALTH</small>
                        <h3 class="fw-bold text-success my-1">{{ $avgBatteryHealth ?? 98.4 }}%</h3>
                        <small class="text-success" style="font-size: 10px;">Zero Degradation Alerts</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border rounded-3 p-3 bg-light text-center">
                        <small class="text-muted d-block" style="font-size: 11px;">AVG CELL TEMP</small>
                        <h3 class="fw-bold text-primary my-1">{{ $avgCellTemp ?? 29.5 }}°C</h3>
                        <small class="text-primary" style="font-size: 10px;">Optimal Thermal Range</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border rounded-3 p-3 bg-light text-center">
                        <small class="text-muted d-block" style="font-size: 11px;">DC FAST CHARGE CHARGES</small>
                        <h3 class="fw-bold text-warning my-1">{{ $totalDcFastCharges ?? 342 }}</h3>
                        <small class="text-muted" style="font-size: 10px;">Hirna High-Power Stations</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border rounded-3 p-3 bg-light text-center">
                        <small class="text-muted d-block" style="font-size: 11px;">REGENERATIVE RECOVERY</small>
                        <h3 class="fw-bold text-info my-1">{{ $regenRecovery ?? 18.2 }}%</h3>
                        <small class="text-info" style="font-size: 10px;">Energy Reclaimed in Transit</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drivers Tab (Team 9 Synced Read-Only View) -->
        <div class="tab-pane fade" id="drivers-pane" role="tabpanel" aria-labelledby="drivers-tab" tabindex="0">
            <div class="alert alert-info border-0 rounded-3 small mb-3" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <strong>Note:</strong> Driver Registration & Account Management is handled by <strong>Team 9 (TNVS Operations & Driver Management System)</strong>. The roster below is a read-only directory synced via API.
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted" style="font-size: 13px;">
                            <th>DRIVER NAME</th>
                            <th>EMAIL</th>
                            <th>LICENSE NUMBER</th>
                            <th>OPERATIONAL STATUS</th>
                            <th>SAFETY SCORE</th>
                            <th>TOTAL DISTANCE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary bg-opacity-10 p-2 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi bi-person-fill text-secondary fs-5"></i>
                                        </div>
                                        <span class="fw-bold">{{ $driver->user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $driver->user->email }}</td>
                                <td class="fw-bold text-uppercase">{{ $driver->license_number }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $driver->status === 'available' ? 'bg-success' : ($driver->status === 'on_trip' ? 'bg-info' : 'bg-secondary') }}">
                                        {{ $driver->status === 'on_trip' ? 'On Trip' : ucfirst($driver->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $driver->performance_score >= 90 ? 'bg-success' : ($driver->performance_score >= 80 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $driver->performance_score }}%
                                    </span>
                                </td>
                                <td>{{ number_format($driver->total_distance_km, 1) }} km</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">No drivers registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog rounded-4 overflow-hidden">
        <div class="modal-content border-0">
            <form action="{{ route('vehicles.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold" id="addVehicleModalLabel"><i class="bi bi-ev-front me-2"></i> Register New Hirna Vehicle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 500;">Make (Brand)</label>
                            <input type="text" name="make" value="Hirna" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 500;">Model (Name)</label>
                            <input type="text" name="model" placeholder="e.g. Nerio Green / VF 8" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 500;">License Plate</label>
                            <input type="text" name="license_plate" placeholder="e.g. NGA-1029" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 500;">Year</label>
                            <input type="number" name="year" value="2026" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 500;">Vehicle Category</label>
                            <select name="type" class="form-select rounded-3" required>
                                <option value="Sedan" selected>Sedan (Nerio Green)</option>
                                <option value="SUV">SUV (VF 8 / VF 9)</option>
                                <option value="Crossover">Crossover (VF e34)</option>
                                <option value="Hatchback">Compact (VF 5)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 500;">Battery Storage (kWh)</label>
                            <input type="number" name="fuel_capacity" placeholder="e.g. 42.0" step="0.1" class="form-control rounded-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-weight: 500;">Initial Status</label>
                            <select name="status" class="form-select rounded-3" required>
                                <option value="active" selected>Active (Available)</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="offline">Offline</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-3"><i class="bi bi-plus-circle me-1"></i> Register Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function filterVehiclesTable() {
    const input = document.getElementById('vehicleSearchInput').value.toLowerCase();
    const filter = document.getElementById('vehicleTypeFilter') ? document.getElementById('vehicleTypeFilter').value.toLowerCase() : '';
    const rows = document.querySelectorAll('#vehicles-pane tbody tr, #pms-pane tbody tr, #battery-pane tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const matchesSearch = text.includes(input);
        const matchesFilter = filter === '' || text.includes(filter);
        row.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
}

function exportVehiclesToCSV() {
    let csv = [];
    const rows = document.querySelectorAll("table tbody tr");
    csv.push('"Vehicle / Details","Fuel / Battery Source","Distance / Odometer","Status / Metrics"');
    rows.forEach(r => {
        if (r.style.display !== 'none') {
            let cols = r.querySelectorAll("td");
            if (cols.length >= 2) {
                let rowData = [];
                cols.forEach(c => rowData.push('"' + c.innerText.replace(/"/g, '""').trim() + '"'));
                csv.push(rowData.join(","));
            }
        }
    });
    const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    const downloadLink = document.createElement("a");
    downloadLink.download = "Hirna_Fleet_Inventory.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.click();
}

function openScheduleModal() {
    const modalEl = document.getElementById('schedulePMSModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}
</script>

<!-- Schedule Maintenance Modal -->
<div class="modal fade" id="schedulePMSModal" tabindex="-1" aria-labelledby="schedulePMSModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg rounded-4 overflow-hidden">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('maintenance.store') }}" method="POST">
                @csrf
                <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #CE2029 0%, #7F1D1D 100%);">
                    <h5 class="modal-title fw-bold" id="schedulePMSModalLabel"><i class="bi bi-wrench-adjustable me-2"></i> Schedule Preventive Maintenance Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Select Hirna Vehicle Unit</label>
                            <select name="vehicle_id" class="form-select rounded-3" required>
                                <option value="" disabled selected>-- Select Vehicle from Fleet --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->make }} {{ $v->model }} (Plate: {{ $v->license_plate }} &bull; Status: {{ ucfirst($v->status) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Service Type</label>
                            <input type="text" name="service_type" placeholder="e.g. Battery Health & System Tune-up, Oil Change, Tire Alignment, Brake Inspection" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Scheduled Service Date</label>
                            <input type="date" name="scheduled_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estimated Cost (₱)</label>
                            <input type="number" name="cost" value="1500" step="0.01" class="form-control rounded-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Initial Maintenance Status</label>
                            <select name="status" class="form-select rounded-3" required>
                                <option value="scheduled" selected>📅 Scheduled (Pending Service)</option>
                                <option value="in_progress">⚙️ In Progress (Sets vehicle offline to Maintenance)</option>
                                <option value="completed">✅ Completed (Releases vehicle to Active)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Service Notes / Details</label>
                            <textarea name="description" rows="3" placeholder="Specify symptoms, spare parts requisitions, or technician notes..." class="form-control rounded-3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold" style="background: #CE2029 !important;"><i class="bi bi-calendar-check me-1"></i> Confirm & Schedule PMS</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
