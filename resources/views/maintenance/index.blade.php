@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-danger text-white px-3 py-1 rounded-pill" style="font-size: 11px; letter-spacing: 0.5px; background: #CE2029 !important;">HIRNA MOBILITY SOLUTIONS INC.</span>
            <span class="text-muted" style="font-size: 12px;"><i class="bi bi-wrench-adjustable text-danger me-1"></i> Fleet Maintenance & Repairs</span>
        </div>
        <h2 class="page-header-title mt-1">Preventive Maintenance Services (PMS)</h2>
        <p class="page-header-subtitle">Schedule engine tune-ups, log service expenses, update maintenance statuses, and monitor fleet vehicle health.</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <div class="input-group" style="max-width: 320px;">
            <input type="text" id="pmsSearchInput" class="form-control rounded-start-3 border-secondary-subtle" placeholder="Search service, vehicle, notes..." onkeyup="filterPmsTable()">
            <button class="btn btn-danger rounded-end-3 fw-bold" type="button" onclick="filterPmsTable()" style="background: #CE2029 !important;">
                <i class="bi bi-search me-1"></i> Search
            </button>
        </div>
        <button type="button" class="btn btn-premium d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#schedulePMSModal" onclick="openScheduleModal();">
            <i class="bi bi-calendar-plus me-1"></i> Schedule Maintenance
        </button>
    </div>
</div>

<!-- Success / Flash Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4 bg-success bg-opacity-10 text-success fw-medium" role="alert">
        <i class="bi bi-check-circle-fill text-success me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Inter-System Integration Connections Badge Banner -->
<div class="alert alert-dark bg-dark text-white border-0 rounded-4 p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-diagram-3-fill text-warning fs-4 me-2"></i>
            <div>
                <span class="fw-bold d-block text-white small">HIRNA MOBILITY INTER-SYSTEM INTEGRATION PIPELINE</span>
                <span class="text-white-50 fw-medium" style="font-size: 11px;">Connected to peer enterprise systems for spare parts requisitions and repair GL expense logging.</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2"><i class="bi bi-cart-plus me-1"></i> Supply Chain Requisitions (PR)</span>
            <span class="badge bg-info text-dark fw-bold px-3 py-2"><i class="bi bi-cash-stack me-1"></i> Financials Repair AP/GL</span>
        </div>
    </div>
</div>

<!-- Scheduled and Log PMS Table Container -->
<div class="card premium-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-wrench-adjustable text-danger me-2"></i> PMS Logs & Service Schedule</h5>
        <span class="badge bg-danger text-white rounded-pill px-3 py-2" style="background: #CE2029 !important;">{{ $records->count() }} Service Records</span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-muted" style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                    <th>VEHICLE</th>
                    <th>SERVICE TYPE</th>
                    <th>DESCRIPTION</th>
                    <th>ESTIMATED COST</th>
                    <th>SCHEDULED DATE</th>
                    <th>COMPLETION DATE</th>
                    <th>MAINTENANCE STATUS</th>
                    <th class="text-end">ACTION / UPDATE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>
                            <span class="fw-bold d-block text-dark">{{ $record->vehicle->make ?? 'Vehicle' }} {{ $record->vehicle->model ?? '' }}</span>
                            <small class="badge bg-secondary text-white mt-1">{{ $record->vehicle->license_plate ?? 'N/A' }}</small>
                        </td>
                        <td class="fw-bold text-dark">{{ $record->service_type }}</td>
                        <td style="font-size: 13px; max-width: 220px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                            {{ $record->description ?: 'Routine Maintenance' }}
                        </td>
                        <td class="fw-bold text-danger">₱{{ number_format($record->cost, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->scheduled_date)->toFormattedDateString() }}</td>
                        <td>
                            {{ $record->completion_date ? \Carbon\Carbon::parse($record->completion_date)->toFormattedDateString() : 'Pending' }}
                        </td>
                        <td>
                            @if($record->status === 'completed')
                                <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> Completed</span>
                            @elseif($record->status === 'in_progress')
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="bi bi-gear-fill spin me-1"></i> In Progress</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-clock-fill me-1"></i> Scheduled</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-3 fw-medium" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $record->id }}">
                                <i class="bi bi-pencil-square me-1"></i> Update Status
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-tools fs-1 d-block mb-2 text-secondary"></i>
                            No maintenance service records logged yet. Click <strong>Schedule Maintenance</strong> to add one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODALS PLACED CLEANLY OUTSIDE TABLE TO PREVENT JS BLOCKING -->
<!-- ======================================================== -->

<!-- Update Status Modals for Each Record -->
@foreach($records as $record)
<div class="modal fade" id="updateStatusModal{{ $record->id }}" tabindex="-1" aria-labelledby="updateStatusLabel{{ $record->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered rounded-4 overflow-hidden">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('maintenance.update-status', $record) }}" method="POST">
                @csrf
                <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #CE2029 0%, #7F1D1D 100%);">
                    <h5 class="modal-title fw-bold" id="updateStatusLabel{{ $record->id }}">
                        <i class="bi bi-wrench me-2"></i> Update Maintenance Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="card border-0 bg-light p-3 mb-3 rounded-3">
                        <span class="small text-muted d-block fw-bold text-uppercase" style="font-size: 10px;">Target Vehicle</span>
                        <span class="fw-bold text-dark">{{ $record->vehicle->make ?? '' }} {{ $record->vehicle->model ?? '' }} ({{ $record->vehicle->license_plate ?? 'N/A' }})</span>
                        <span class="small text-muted mt-1 d-block">Service: <strong>{{ $record->service_type }}</strong></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Select New Status</label>
                        <select name="status" class="form-select rounded-3" id="statusSelect{{ $record->id }}" onchange="toggleCompletionDate({{ $record->id }});" required>
                            <option value="scheduled" {{ $record->status === 'scheduled' ? 'selected' : '' }}>📅 Scheduled (Pending Service)</option>
                            <option value="in_progress" {{ $record->status === 'in_progress' ? 'selected' : '' }}>⚙️ In Progress (Takes Vehicle Offline to Maintenance)</option>
                            <option value="completed" {{ $record->status === 'completed' ? 'selected' : '' }}>✅ Completed (Releases Vehicle to Active Fleet)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Final Repair / Service Cost (₱)</label>
                        <input type="number" step="0.01" name="cost" value="{{ $record->cost }}" class="form-control rounded-3" required>
                    </div>

                    <div class="mb-3 {{ $record->status === 'completed' ? '' : 'd-none' }}" id="completionDateDiv{{ $record->id }}">
                        <label class="form-label fw-bold">Completion Date</label>
                        <input type="date" name="completion_date" class="form-control rounded-3" value="{{ $record->completion_date ? date('Y-m-d', strtotime($record->completion_date)) : date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold" style="background: #CE2029 !important;">
                        <i class="bi bi-check2-circle me-1"></i> Save Status Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Schedule PMS Modal -->
<div class="modal fade" id="schedulePMSModal" tabindex="-1" aria-labelledby="schedulePMSLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg rounded-4 overflow-hidden">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('maintenance.store') }}" method="POST">
                @csrf
                <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #CE2029 0%, #7F1D1D 100%);">
                    <h5 class="modal-title fw-bold" id="schedulePMSLabel">
                        <i class="bi bi-calendar-plus me-2"></i> Schedule Preventive Maintenance Service
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Select Fleet Vehicle</label>
                            <select name="vehicle_id" class="form-select rounded-3" required>
                                <option value="" disabled selected>-- Select Vehicle from Hirna Fleet --</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->make }} {{ $vehicle->model }} (Plate: {{ $vehicle->license_plate }} &bull; Current Status: {{ ucfirst($vehicle->status) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Service Type</label>
                            <input type="text" name="service_type" placeholder="e.g. Engine Tune-up, Oil Change, Tire Alignment, Battery Check" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Scheduled Service Date</label>
                            <input type="date" name="scheduled_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estimated Cost (₱)</label>
                            <input type="number" step="0.01" name="cost" placeholder="e.g. 3500.00" class="form-control rounded-3" required>
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
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold" style="background: #CE2029 !important;">
                        <i class="bi bi-calendar-check me-1"></i> Confirm & Schedule PMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    function filterPmsTable() {
        const input = document.getElementById('pmsSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
</script>
@endsection
