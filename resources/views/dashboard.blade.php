@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white px-3 py-1 rounded-pill" style="font-size: 11px; letter-spacing: 0.5px; background: #CE2029 !important;">HIRNA MOBILITY SOLUTIONS INC.</span>
            <span class="text-muted" style="font-size: 12px;"><i class="bi bi-telephone-fill text-danger me-1"></i> 24/7 Booking Hotline: (02) 8888-HIRNA</span>
        </div>
        <h2 class="page-header-title mt-1">Hirna Fleet Analytics Dashboard</h2>
        <p class="page-header-subtitle">Real-time performance metrics, taxi & vehicle fleet status, AI fuel & energy predictions, and transport cost analytics.</p>
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
    <!-- Active Electric Fleet -->
    <div class="col-md-3">
        <div class="card premium-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Active Electric Fleet</span>
                    <h3 class="fw-bold mt-1 mb-0 text-primary">{{ $activeVehicles }}<span class="fs-6 text-muted font-normal"> / {{ $totalVehicles }} units</span></h3>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-ev-front fs-3 text-primary"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-success rounded-pill">{{ $activeVehicles }} Active Hirna Vehicles</span>
                <span class="badge bg-warning text-dark rounded-pill">{{ $maintenanceVehicles }} PMS Servicing</span>
            </div>
        </div>
    </div>

    <!-- Active Dispatches -->
    <div class="col-md-3">
        <div class="card premium-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Active Dispatches</span>
                    <h3 class="fw-bold mt-1 mb-0 text-info">{{ $activeTrips }}</h3>
                </div>
                <div class="bg-info bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-geo-alt fs-3 text-info"></i>
                </div>
            </div>
            <div class="mt-3 text-muted" style="font-size: 13px;">
                <span class="loader-pulse me-1"></span> <span class="fw-bold text-danger">Live GPS</span> tracking online
            </div>
        </div>
    </div>

    <!-- EV Energy Charging Expenses -->
    <div class="col-md-3">
        <div class="card premium-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Total Charging Expense</span>
                    <h3 class="fw-bold mt-1 mb-0 text-success">₱{{ number_format($totalFuelCost, 2) }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-lightning-charge-fill fs-3 text-success"></i>
                </div>
            </div>
            <div class="mt-3 text-muted" style="font-size: 13px;">
                Energy Consumed: <strong class="text-dark">{{ number_format($totalFuelLiters, 1) }} kWh</strong>
            </div>
        </div>
    </div>

    <!-- Odometer Avg EV Efficiency -->
    <div class="col-md-3">
        <div class="card premium-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">EV Energy Consumption</span>
                    <h3 class="fw-bold mt-1 mb-0 text-dark">{{ number_format($avgEfficiency, 2) }} <span class="fs-6 text-muted fw-normal">kWh/100km</span></h3>
                </div>
                <div class="bg-dark bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-speedometer2 fs-3 text-dark"></i>
                </div>
            </div>
            <div class="mt-3 text-muted" style="font-size: 13px;">
                Maintenance Expense: <strong class="text-danger">₱{{ number_format($totalMaintenanceCost, 2) }}</strong>
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
                    <p class="text-muted small mb-0">Active vehicle lineup deployed across Hirna regional transport hubs.</p>
                </div>
                <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-medium">
                    Manage Fleet Inventory <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="text-muted" style="font-size: 12px; font-weight: 700;">
                            <th>HIRNA MODEL</th>
                            <th>LICENSE PLATE</th>
                            <th>EV CATEGORY</th>
                            <th>BATTERY CAPACITY</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vinfastFleet as $ev)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 p-2 rounded-3 me-2">
                                            <i class="bi bi-ev-front-fill text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block text-dark" style="font-size: 14px;">{{ $ev->make }} {{ $ev->model }}</strong>
                                            <small class="text-muted" style="font-size: 11px;">Cyan Fleet Unit &bull; Year {{ $ev->year }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-dark px-3 py-2 rounded-3 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">{{ $ev->license_plate }}</span>
                                </td>
                                <td style="font-size: 13px;" class="fw-semibold text-secondary">{{ $ev->type }}</td>
                                <td style="font-size: 13px;" class="fw-bold text-success">
                                    <i class="bi bi-battery-charging me-1"></i> {{ $ev->fuel_capacity }} kWh
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

<!-- Graphs and Data Lists -->
<div class="row mb-4">
    <!-- Chart 1: Energy Expense Trend -->
    <div class="col-md-8">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-graph-up text-primary me-2"></i> EV Charging Expense & kWh Trend</h5>
            <div style="position: relative; height: 300px;">
                <canvas id="costHistoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Energy Consumption by Hirna Class -->
    <div class="col-md-4">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i> Energy by Hirna Class</h5>
            <div style="position: relative; height: 220px;" class="d-flex align-items-center justify-content-center">
                <canvas id="fuelTypeChart"></canvas>
            </div>
            <div class="mt-3 text-center text-muted" style="font-size: 12px;">
                Cumulative kWh energy consumed per Hirna Vehicle category.
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Drivers Standings -->
    <div class="col-md-6">
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
                                        <span class="fw-bold" style="font-size: 14px;">{{ $driver->user->name }}</span>
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

    <!-- Active Alerts / Service Warnings -->
    <div class="col-md-6">
        <div class="card premium-card p-4 h-100">
            <h5 class="fw-bold mb-4 text-danger"><i class="bi bi-bell-fill me-2"></i> Maintenance Alerts & PMS Panel</h5>
            @if(count($pendingMaintenance) > 0)
                <div class="list-group list-group-flush">
                    @foreach($pendingMaintenance as $record)
                        <div class="list-group-item px-0 py-3 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">{{ $record->service_type }}</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">
                                        Vehicle: <strong>{{ $record->vehicle->make }} {{ $record->vehicle->model }} ({{ $record->vehicle->license_plate }})</strong>
                                    </p>
                                    <span class="text-danger" style="font-size: 12px; font-weight: 500;">
                                        <i class="bi bi-calendar-event me-1"></i> Scheduled Date: {{ \Carbon\Carbon::parse($record->scheduled_date)->toFormattedDateString() }}
                                    </span>
                                </div>
                                <span class="badge bg-danger rounded-pill">Urgent PMS</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark">All Hirna Units Clear</h6>
                    <p class="text-muted mb-0" style="font-size: 13px;">No vehicles scheduled for PMS in the next 3 days.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 1. Chart 1: Cost History Chart (Line Chart)
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
                    label: 'Charging Expense (₱)',
                    data: costData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                },
                {
                    label: 'Energy Consumed (kWh)',
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
                        text: 'Energy (kWh)',
                        font: { family: 'Outfit' }
                    }
                }
            }
        }
    });

    // 2. Chart 2: Fuel/Energy Distribution by Type (Doughnut Chart)
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
                    '#10B981',
                    '#0284C7',
                    '#6366F1',
                    '#F59E0B',
                    '#EC4899'
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
