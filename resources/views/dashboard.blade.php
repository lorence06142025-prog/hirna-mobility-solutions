@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white px-3 py-1 rounded-pill" style="font-size: 11px; letter-spacing: 0.5px; background: #CE2029 !important;">HIRNA MOBILITY SOLUTIONS INC.</span>
            <span class="text-muted" style="font-size: 12px;"><i class="bi bi-telephone-fill text-danger me-1"></i> 24/7 Booking Hotline: (02) 8888-HIRNA</span>
        </div>
        <h2 class="page-header-title mt-1">Hirna Fleet Analytics & Maintenance Dashboard</h2>
        <p class="page-header-subtitle">Real-time performance metrics, taxi & vehicle fleet status, Gasoline & EV fuel tracking, PMS maintenance logs, and transport cost analytics.</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <button class="btn btn-outline-success rounded-3 fw-bold shadow-sm" onclick="exportDashboardToCSV();">
            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV / Excel
        </button>
        <button class="btn btn-outline-dark rounded-3 fw-bold shadow-sm" onclick="window.print();">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
        </button>
        <button class="btn btn-premium d-flex align-items-center" onclick="window.location.reload();">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
        </button>
    </div>
</div>

<!-- KPI Cards Grid -->
<div class="row mb-4">
    <!-- Active Fleet Status -->
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Active Hirna Fleet</span>
                    <h3 class="fw-bold mt-1 mb-0 text-primary">{{ $activeVehicles }}<span class="fs-6 text-muted font-normal"> / {{ $totalVehicles }} units</span></h3>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-car-front-fill fs-3 text-primary"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-success rounded-pill me-1">{{ $activeVehicles }} Active Vehicles</span>
                <span class="badge bg-warning text-dark rounded-pill">{{ $maintenanceVehicles }} PMS Servicing</span>
            </div>
        </div>
    </div>

    <!-- Active Dispatches & Live GPS -->
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Active Dispatches</span>
                    <h3 class="fw-bold mt-1 mb-0 text-info">{{ $activeTrips }}</h3>
                </div>
                <div class="bg-info bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-geo-alt-fill fs-3 text-info"></i>
                </div>
            </div>
            <div class="mt-3 text-muted" style="font-size: 13px;">
                <span class="loader-pulse me-1"></span> <span class="fw-bold text-danger">Live GPS</span> Telemetry Online
            </div>
        </div>
    </div>

    <!-- Dual Fuel & Energy Expenses (Gasoline/Diesel + EV Electric) -->
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Total Fuel & Energy Expense</span>
                    <h3 class="fw-bold mt-1 mb-0 text-success">₱{{ number_format($totalFuelCost, 2) }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-fuel-pump-fill fs-3 text-success"></i>
                </div>
            </div>
            <div class="mt-3 text-muted" style="font-size: 11.5px;">
                <div class="d-flex justify-content-between mb-1">
                    <span>⛽ <strong>Gasoline/Diesel:</strong></span>
                    <span class="text-dark fw-bold">{{ number_format($gasolineLiters, 1) }} L (₱{{ number_format($gasolineCost, 2) }})</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>⚡ <strong>EV Charging:</strong></span>
                    <span class="text-dark fw-bold">{{ number_format($evKwh, 1) }} kWh (₱{{ number_format($evCost, 2) }})</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance & Energy Consumption Rates -->
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Maintenance Expense</span>
                    <h3 class="fw-bold mt-1 mb-0 text-danger">₱{{ number_format($totalMaintenanceCost, 2) }}</h3>
                </div>
                <div class="bg-danger bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-wrench-adjustable-circle-fill fs-3 text-danger"></i>
                </div>
            </div>
            <div class="mt-3 text-muted" style="font-size: 11.5px;">
                <div class="d-flex justify-content-between mb-1">
                    <span>⛽ Fuel Consumed:</span>
                    <strong class="text-dark">{{ number_format($gasolineLiters, 1) }} Liters</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>⚡ Energy Consumed:</span>
                    <strong class="text-dark">{{ number_format($evKwh, 1) }} kWh</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hirna Vehicle Fleet Inventory Overview Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card premium-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-shield-shaded text-danger me-2"></i> Hirna Mobility Fleet Inventory</h5>
                    <p class="text-muted small mb-0">Active vehicle lineup deployed across Hirna regional transport hubs (Gasoline Taxis, MPVs, & EVs).</p>
                </div>
                @if(in_array(session('user_role', 'admin'), ['admin', 'fleet_manager']))
                <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-medium">
                    Manage Fleet Inventory <i class="bi bi-arrow-right ms-1"></i>
                </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="text-muted" style="font-size: 12px; font-weight: 700;">
                            <th>HIRNA MODEL</th>
                            <th>LICENSE PLATE</th>
                            <th>PROPULSION & CATEGORY</th>
                            <th>TANK / BATTERY CAPACITY</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vinfastFleet as $ev)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger bg-opacity-10 p-2 rounded-3 me-2">
                                            <i class="bi {{ str_contains(strtolower($ev->model), 'ev') || str_contains(strtolower($ev->make), 'vinfast') ? 'bi-ev-front-fill text-success' : 'bi-car-front-fill text-danger' }} fs-5"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block text-dark" style="font-size: 14px;">{{ $ev->make }} {{ $ev->model }}</strong>
                                            <small class="text-muted" style="font-size: 11px;">Hirna Fleet Unit &bull; Year {{ $ev->year }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-dark px-3 py-2 rounded-3 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">{{ $ev->license_plate }}</span>
                                </td>
                                <td style="font-size: 13px;" class="fw-semibold text-secondary">
                                    @if(str_contains(strtolower($ev->model), 'ev') || str_contains(strtolower($ev->make), 'vinfast'))
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="bi bi-lightning-charge-fill me-1"></i> Electric Vehicle (EV)</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="bi bi-fuel-pump-fill me-1"></i> {{ $ev->type }}</span>
                                    @endif
                                </td>
                                <td style="font-size: 13px;" class="fw-bold text-dark">
                                    @if(str_contains(strtolower($ev->model), 'ev') || str_contains(strtolower($ev->make), 'vinfast'))
                                        <i class="bi bi-battery-charging text-success me-1"></i> {{ $ev->fuel_capacity }} kWh
                                    @else
                                        <i class="bi bi-fuel-pump text-danger me-1"></i> {{ $ev->fuel_capacity }} Liters
                                    @endif
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $ev->status == 'active' ? 'bg-success' : ($ev->status == 'maintenance' ? 'bg-warning text-dark' : 'bg-secondary') }} px-3 py-1">
                                        <i class="bi {{ $ev->status == 'active' ? 'bi-check-circle-fill' : 'bi-wrench-adjustable' }} me-1"></i> {{ ucfirst($ev->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No Hirna vehicles registered in fleet inventory.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Alerts & Maintenance Log Panels -->
<div class="row mb-4">
    <!-- Active PMS Maintenance Alerts & Notifications -->
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card premium-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-bell-fill me-2"></i> PMS Maintenance Alerts</h5>
                @if(in_array(session('user_role', 'admin'), ['admin', 'fleet_manager']))
                <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-outline-danger rounded-3 px-3 fw-bold">
                    Schedule PMS <i class="bi bi-arrow-right ms-1"></i>
                </a>
                @endif
            </div>

            @if(count($pendingMaintenance) > 0)
                <div class="list-group list-group-flush">
                    @foreach($pendingMaintenance as $record)
                        <div class="list-group-item px-0 py-3 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="fw-bold mb-0 text-dark">{{ $record->service_type }}</h6>
                                        <span class="badge {{ $record->status == 'in_progress' ? 'bg-primary' : 'bg-warning text-dark' }} rounded-pill" style="font-size: 10px;">
                                            {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                        </span>
                                    </div>
                                    <p class="mb-1 text-muted mt-1" style="font-size: 13px;">
                                        Vehicle: <strong>{{ $record->vehicle ? $record->vehicle->make . ' ' . $record->vehicle->model : 'Vehicle #' . $record->vehicle_id }} ({{ $record->vehicle ? $record->vehicle->license_plate : 'N/A' }})</strong>
                                    </p>
                                    <small class="text-secondary d-block mb-1">{{ $record->description }}</small>
                                    <span class="text-danger fw-semibold" style="font-size: 12px;">
                                        <i class="bi bi-calendar-event me-1"></i> Scheduled: {{ \Carbon\Carbon::parse($record->scheduled_date)->format('M d, Y') }} &bull; Estimated Cost: ₱{{ number_format($record->cost, 2) }}
                                    </span>
                                </div>
                                <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold" style="font-size: 11px;">PMS Action Needed</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark">All Hirna Fleet Units Clear</h6>
                    <p class="text-muted mb-0" style="font-size: 13px;">No vehicles scheduled for PMS maintenance.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Maintenance History Log -->
    <div class="col-md-6">
        <div class="card premium-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journals me-2 text-primary"></i> Maintenance Log History</h5>
                @if(in_array(session('user_role', 'admin'), ['admin', 'fleet_manager']))
                <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-outline-dark rounded-3 px-3 fw-medium">
                    View All Logs <i class="bi bi-arrow-right ms-1"></i>
                </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size: 13px;">
                    <thead>
                        <tr class="text-muted" style="font-size: 11px; font-weight: 700;">
                            <th>VEHICLE</th>
                            <th>SERVICE TYPE</th>
                            <th>COST</th>
                            <th>DATE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenanceLogs as $mLog)
                            <tr>
                                <td>
                                    <strong class="d-block text-dark">{{ $mLog->vehicle ? $mLog->vehicle->license_plate : 'N/A' }}</strong>
                                    <small class="text-muted">{{ $mLog->vehicle ? $mLog->vehicle->model : '' }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $mLog->service_type }}</span>
                                </td>
                                <td class="fw-bold text-danger">₱{{ number_format($mLog->cost, 2) }}</td>
                                <td class="text-muted" style="font-size: 12px;">{{ \Carbon\Carbon::parse($mLog->scheduled_date)->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $mLog->status == 'completed' ? 'bg-success' : ($mLog->status == 'in_progress' ? 'bg-primary' : 'bg-warning text-dark') }} px-2 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $mLog->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No maintenance history recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Graphs & Data Analytics -->
<div class="row mb-4">
    <!-- Chart 1: Dual Fuel & Energy Expense Trend -->
    <div class="col-md-8 mb-3 mb-md-0">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-graph-up text-primary me-2"></i> Dual Fuel & Energy Expense Trend (Gasoline & EV)</h5>
            <div style="position: relative; height: 300px;">
                <canvas id="costHistoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Consumption by Hirna Vehicle Class -->
    <div class="col-md-4">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i> Fuel & Energy by Vehicle Class</h5>
            <div style="position: relative; height: 220px;" class="d-flex align-items-center justify-content-center">
                <canvas id="fuelTypeChart"></canvas>
            </div>
            <div class="mt-3 text-center text-muted" style="font-size: 12px;">
                Cumulative Liters & kWh consumed per Hirna vehicle category.
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Drivers Standings -->
    <div class="col-md-12">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-4"><i class="bi bi-star-fill text-warning me-2"></i> Top Driver Standings & Safety Scores</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted" style="font-size: 12px; font-weight: 600;">
                            <th>DRIVER</th>
                            <th>LICENSE</th>
                            <th>COMPLETED TRIPS</th>
                            <th>SAFETY SCORE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topDrivers as $driver)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary bg-opacity-10 p-2 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-person-fill text-secondary"></i>
                                        </div>
                                        <span class="fw-bold" style="font-size: 14px;">{{ $driver->user ? $driver->user->name : 'Driver #' . $driver->id }}</span>
                                    </div>
                                </td>
                                <td style="font-size: 13px;">{{ $driver->license_number }}</td>
                                <td style="font-size: 13px;" class="fw-bold">{{ $driver->total_trips }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $driver->performance_score >= 90 ? 'bg-success' : ($driver->performance_score >= 80 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $driver->performance_score }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No drivers registered.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 1. Chart 1: Dual Fuel & Energy Expense History (Line Chart)
    const costCtx = document.getElementById('costHistoryChart').getContext('2d');
    
    const costDates = {!! json_encode($costHistory->pluck('date')) !!};
    const costData = {!! json_encode($costHistory->pluck('daily_cost')) !!};
    const litersData = {!! json_encode($costHistory->pluck('daily_liters')) !!};

    new Chart(costCtx, {
        type: 'line',
        data: {
            labels: costDates,
            datasets: [
                {
                    label: 'Daily Expense (₱)',
                    data: costData,
                    borderColor: '#CE2029',
                    backgroundColor: 'rgba(206, 32, 41, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                },
                {
                    label: 'Fuel & Energy Volume (Liters / kWh)',
                    data: litersData,
                    borderColor: '#0284C7',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            family: 'Outfit'
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Cost (₱)',
                        font: { family: 'Outfit' }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Volume (L / kWh)',
                        font: { family: 'Outfit' }
                    }
                }
            }
        }
    });

    // 2. Chart 2: Fuel/Energy Distribution by Hirna Vehicle Class (Doughnut Chart)
    const fuelCtx = document.getElementById('fuelTypeChart').getContext('2d');
    const fuelTypes = {!! json_encode($fuelByType->pluck('type')) !!};
    const fuelTotals = {!! json_encode($fuelByType->pluck('total_liters')) !!};

    new Chart(fuelCtx, {
        type: 'doughnut',
        data: {
            labels: fuelTypes,
            datasets: [{
                data: fuelTotals,
                backgroundColor: [
                    '#CE2029',
                    '#10B981',
                    '#0284C7',
                    '#F59E0B',
                    '#8B5CF6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Outfit'
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    function exportDashboardToCSV() {
        let csv = [];
        const tables = document.querySelectorAll("table");
        tables.forEach((table, index) => {
            csv.push(`"--- TABLE ${index + 1} ---"`);
            const rows = table.querySelectorAll("tr");
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length; j++) 
                    row.push('"' + cols[j].innerText.replace(/"/g, '""').trim() + '"');
                csv.push(row.join(","));
            }
            csv.push("");
        });

        const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        const downloadLink = document.createElement("a");
        downloadLink.download = "Hirna_Fleet_Analytics_Dashboard.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
</script>
@endsection
