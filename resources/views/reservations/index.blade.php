@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="page-header-title">Vehicle Reservation and Dispatch</h2>
        <p class="page-header-subtitle">Schedule, approve, and track Hirna Vehicle vehicle bookings for dispatches.</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <div class="input-group" style="max-width: 340px;">
            <input type="text" id="reservationSearchInput" class="form-control rounded-start-3 border-secondary-subtle" placeholder="Search reservation, plate, driver..." onkeyup="filterReservationsTable()">
            <button class="btn btn-danger rounded-end-3 fw-bold" type="button" onclick="filterReservationsTable()" style="background: #CE2029 !important;">
                <i class="bi bi-search me-1"></i> Search
            </button>
        </div>
        <button class="btn btn-premium rounded-3 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#newReservationModal">
            <i class="bi bi-plus-circle me-1"></i> New Vehicle Reservation
        </button>
    </div>
</div>

<!-- Inter-System Integration Connections Badge Banner -->
<div class="alert alert-dark bg-dark text-white border-0 rounded-4 p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-diagram-3-fill text-success fs-4 me-2"></i>
            <div>
                <span class="fw-bold d-block text-white small">INTER-SYSTEM INTEGRATION PIPELINE (TEAM 7 &bull; VRDS)</span>
                <span class="text-white fw-medium" style="font-size: 11px;">Connected to peer enterprise systems for passenger booking dispatches, driver duty verification, and facilities reservations.</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-info text-dark fw-bold px-3 py-2"><i class="bi bi-clock-history me-1"></i> Team 2: HRMS Shift Rosters</span>
            <span class="badge bg-success text-white fw-bold px-3 py-2"><i class="bi bi-building-check me-1"></i> Team 8: Facilities Bookings</span>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2"><i class="bi bi-phone me-1"></i> Team 10: Passenger Booking API</span>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card premium-card p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Booking Requests</span>
                    <h3 class="fw-bold my-1">{{ $reservations->count() }}</h3>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-4 fs-4">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card premium-card p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Pending Approval</span>
                    <h3 class="fw-bold my-1 text-warning">{{ $reservations->where('status', 'pending')->count() }}</h3>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-4 fs-4">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card premium-card p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Approved & Active</span>
                    <h3 class="fw-bold my-1 text-success">{{ $reservations->where('status', 'approved')->count() }}</h3>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-4 fs-4">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card premium-card p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Available Vehicles Today</span>
                    <h3 class="fw-bold my-1 text-info">{{ $vehicles->count() }}</h3>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-4 fs-4">
                    <i class="bi bi-truck"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOP SECTION: Full-Width Visual Vehicle Schedule Calendar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card premium-card border-0 p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-calendar3 text-success me-2"></i> Hirna Vehicle Vehicle Schedule Calendar</h5>
                    <p class="small text-muted mb-0">Click any date to inspect reserved and available Hirna Vehicle cars in real time.</p>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-2 fs-6">
                    <i class="bi bi-broadcast me-1"></i> Live VRDS Schedule Sync
                </span>
            </div>
            
            <div id="reservationCalendar" style="min-height: 420px;"></div>
        </div>
    </div>
</div>

<!-- BOTTOM SECTION: 2-Column Split (Reservations Table + Availability Lookup) -->
<div class="row g-4">
    <!-- Reservations List Table -->
    <div class="col-lg-8">
        <div class="card premium-card border-0 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Reservation Records</h5>
                <span class="badge bg-light text-dark border">VRDS Real-Time Schedule</span>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Vehicle</th>
                            <th>Purpose</th>
                            <th>Date & Time</th>
                            <th>Assigned Driver</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $res)
                        <tr>
                            <td>
                                <div>
                                    <strong class="d-block text-dark">{{ $res->vehicle->license_plate }}</strong>
                                    <small class="text-muted">{{ $res->vehicle->make }} {{ $res->vehicle->model }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $res->purpose }}</span>
                                @if($res->remarks)
                                <br><small class="text-muted">{{ Str::limit($res->remarks, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i> {{ \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y') }}
                                    <br>
                                    <i class="bi bi-clock me-1 text-muted"></i> {{ $res->start_time }} - {{ $res->end_time }}
                                </div>
                            </td>
                            <td>
                                @if($res->driver)
                                    <span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i> {{ $res->driver->user->name ?? 'Driver #'.$res->driver_id }}</span>
                                @else
                                    <span class="text-muted small">Auto-Assign on Dispatch</span>
                                @endif
                            </td>
                            <td>
                                @if($res->status == 'approved')
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                @elseif($res->status == 'pending')
                                    <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                @elseif($res->status == 'rejected')
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                                @elseif($res->status == 'completed')
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill"><i class="bi bi-flag me-1"></i> Completed</span>
                                @else
                                    <span class="badge bg-light text-dark px-3 py-2 rounded-pill">{{ ucfirst($res->status) }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                 @if($res->status == 'pending')
                                     <!-- Trigger Approval Confirmation Modal -->
                                     <button type="button" class="btn btn-sm btn-outline-success rounded-3 me-1" data-bs-toggle="modal" data-bs-target="#confirmApproveModal_{{ $res->id }}">
                                         <i class="bi bi-check-lg"></i> Approve
                                     </button>

                                     <!-- Trigger Rejection Confirmation Modal -->
                                     <button type="button" class="btn btn-sm btn-outline-danger rounded-3" data-bs-toggle="modal" data-bs-target="#confirmRejectModal_{{ $res->id }}">
                                         <i class="bi bi-x-lg"></i> Reject
                                     </button>

                                     <!-- Modal: Confirm Approval -->
                                     <div class="modal fade text-start" id="confirmApproveModal_{{ $res->id }}" tabindex="-1" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered">
                                             <div class="modal-content rounded-4 border-0 shadow">
                                                 <div class="modal-header bg-success text-white">
                                                     <h6 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i> Confirm Reservation Approval (#{{ $res->id }})</h6>
                                                     <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                 </div>
                                                 <form action="{{ route('reservations.update-status', $res->id) }}" method="POST" onsubmit="return confirm('🟢 Final Confirmation:\nAre you sure you want to approve this vehicle reservation request?');">
                                                     @csrf
                                                     <input type="hidden" name="status" value="approved">
                                                     <div class="modal-body p-4">
                                                         <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-dark rounded-3 mb-3 small">
                                                             <i class="bi bi-info-circle-fill text-success me-1"></i> You are authorizing this Hirna Vehicle vehicle reservation dispatch.
                                                         </div>
                                                         <div class="card bg-light border-0 p-3 mb-3 small text-dark">
                                                             <div class="row g-2">
                                                                 <div class="col-6"><strong>Requester:</strong> {{ $res->requestedBy->name ?? 'Staff User' }}</div>
                                                                 <div class="col-6"><strong>Vehicle:</strong> {{ $res->vehicle->license_plate ?? 'N/A' }} ({{ $res->vehicle->make ?? '' }} {{ $res->vehicle->model ?? '' }})</div>
                                                                 <div class="col-6"><strong>Date:</strong> {{ \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y') }}</div>
                                                                 <div class="col-6"><strong>Time:</strong> {{ $res->start_time }} - {{ $res->end_time }}</div>
                                                                 <div class="col-12"><strong>Purpose:</strong> {{ $res->purpose }}</div>
                                                             </div>
                                                         </div>
                                                         <div class="mb-3">
                                                             <label class="form-label small fw-bold">Approval Notes / Dispatch Instructions (Optional)</label>
                                                             <textarea name="approval_notes" class="form-control rounded-3" rows="2" placeholder="e.g., Authorized for official transit. Vehicle fully charged."></textarea>
                                                         </div>
                                                     </div>
                                                     <div class="modal-footer bg-light border-0">
                                                         <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                                                         <button type="submit" class="btn btn-success rounded-3 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Confirm & Approve Reservation</button>
                                                     </div>
                                                 </form>
                                             </div>
                                         </div>
                                     </div>

                                     <!-- Modal: Confirm Rejection -->
                                     <div class="modal fade text-start" id="confirmRejectModal_{{ $res->id }}" tabindex="-1" aria-hidden="true">
                                         <div class="modal-dialog modal-dialog-centered">
                                             <div class="modal-content rounded-4 border-0 shadow">
                                                 <div class="modal-header bg-danger text-white">
                                                     <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Reservation Rejection (#{{ $res->id }})</h6>
                                                     <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                 </div>
                                                 <form action="{{ route('reservations.update-status', $res->id) }}" method="POST" onsubmit="return confirm('⚠️ Final Confirmation:\nAre you sure you want to REJECT this vehicle reservation request?');">
                                                     @csrf
                                                     <input type="hidden" name="status" value="rejected">
                                                     <div class="modal-body p-4">
                                                         <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-dark rounded-3 mb-3 small">
                                                             <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Rejecting this request will cancel the reservation and notify the requester.
                                                         </div>
                                                         <div class="card bg-light border-0 p-3 mb-3 small text-dark">
                                                             <div class="row g-2">
                                                                 <div class="col-6"><strong>Requester:</strong> {{ $res->requestedBy->name ?? 'Staff User' }}</div>
                                                                 <div class="col-6"><strong>Vehicle:</strong> {{ $res->vehicle->license_plate ?? 'N/A' }}</div>
                                                                 <div class="col-12"><strong>Purpose:</strong> {{ $res->purpose }}</div>
                                                             </div>
                                                         </div>
                                                         <div class="mb-3">
                                                             <label class="form-label small fw-bold text-danger">Reason for Rejection (Required)</label>
                                                             <textarea name="rejection_reason" class="form-control rounded-3 border-danger-subtle" rows="2" required placeholder="e.g., Vehicle scheduled for Preventive Maintenance Service (PMS)."></textarea>
                                                         </div>
                                                     </div>
                                                     <div class="modal-footer bg-light border-0">
                                                         <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                                                         <button type="submit" class="btn btn-danger rounded-3 fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Confirm & Reject Request</button>
                                                     </div>
                                                 </form>
                                             </div>
                                         </div>
                                     </div>
                                 @elseif($res->status == 'approved')
                                 <form action="{{ route('reservations.update-status', $res->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm marking Reservation #{{ $res->id }} as Completed?');">
                                     @csrf
                                     <input type="hidden" name="status" value="completed">
                                     <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3">
                                         Mark Completed
                                     </button>
                                 </form>
                                 @else
                                 <span class="text-muted small">No actions</span>
                                 @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No vehicle reservations submitted yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Side: Quick Availability Lookup & Rules -->
    <div class="col-lg-4">
        <!-- Quick Availability Lookup -->
        <div class="card premium-card border-0 p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-calendar-range text-primary me-2"></i> Check Vehicle Availability</h5>
            <div class="mb-3">
                <label class="form-label text-muted small fw-semibold">Select Reservation Date</label>
                <input type="date" id="checkDateInput" class="form-control rounded-3" value="{{ date('Y-m-d') }}">
            </div>
            <button type="button" id="btnCheckSlot" onclick="checkAvailability()" class="btn btn-primary w-100 rounded-3 mb-3 fw-bold shadow-sm">
                <i class="bi bi-calendar-check me-1"></i> Check Schedule Slot
            </button>
            <div id="availabilityResults" class="d-none">
                <h6 class="fw-bold text-success small mb-2"><i class="bi bi-check2-circle me-1"></i> Available Hirna Vehicles:</h6>
                <div id="availableList" class="mb-3 d-flex flex-wrap gap-1"></div>
                <h6 class="fw-bold text-danger small mb-2"><i class="bi bi-exclamation-circle me-1"></i> Already Reserved:</h6>
                <div id="reservedList" class="d-flex flex-wrap gap-1"></div>
            </div>
        </div>

        <!-- VRDS Dispatch Rules Info Card -->
        <div class="card premium-card border-0 p-4 bg-dark text-white">
            <h5 class="fw-bold mb-2 text-info"><i class="bi bi-shield-check me-2"></i> VRDS Auto-Conflict Engine</h5>
            <p class="small text-white-50 mb-3">
                The VRDS engine prevents double-booking by enforcing automated schedule validation before confirming reservations.
            </p>
            <ul class="small text-white-50 ps-3 mb-0">
                <li class="mb-1">Checks start & end time overlaps per vehicle.</li>
                <li class="mb-1">Integrates with active dispatch schedules.</li>
                <li>Validates vehicle operational & maintenance status.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal: New Vehicle Reservation -->
<div class="modal fade" id="newReservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i> Request Vehicle Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('reservations.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Vehicle</label>
                        <select name="vehicle_id" class="form-select rounded-3" required>
                            <option value="">-- Choose Active Vehicle --</option>
                            @foreach($vehicles as $veh)
                            <option value="{{ $veh->id }}">{{ $veh->license_plate }} - {{ $veh->make }} {{ $veh->model }} ({{ $veh->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reservation Purpose</label>
                        <input type="text" name="purpose" class="form-control rounded-3" placeholder="e.g. VIP Client Transfer / Scheduled Dispatch" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reservation Date</label>
                            <input type="date" name="reservation_date" class="form-control rounded-3" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign Driver (Optional)</label>
                            <select name="driver_id" class="form-select rounded-3">
                                <option value="">-- Auto Assign Later --</option>
                                @foreach($drivers as $drv)
                                <option value="{{ $drv->id }}">{{ $drv->user->name ?? 'Driver #'.$drv->id }} (Score: {{ $drv->safety_score }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Time</label>
                            <input type="time" name="start_time" class="form-control rounded-3" value="08:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">End Time</label>
                            <input type="time" name="end_time" class="form-control rounded-3" value="17:00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Additional Remarks</label>
                        <textarea name="remarks" class="form-control rounded-3" rows="2" placeholder="Special requirements, passenger count, destination..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">Submit Reservation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
let reservationCalendarInstance = null;

function checkAvailability() {
    const btn = document.getElementById('btnCheckSlot');
    const input = document.getElementById('checkDateInput');
    const date = input ? input.value : '';

    if (!date) {
        alert('Please select a reservation date to check schedule slots!');
        return;
    }

    const originalHtml = btn ? btn.innerHTML : '<i class="bi bi-calendar-check me-1"></i> Check Schedule Slot';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Checking Availability...';
    }

    fetch(`{{ route('reservations.check-availability') }}?date=${date}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(data => {
            const results = document.getElementById('availabilityResults');
            const availList = document.getElementById('availableList');
            const resList = document.getElementById('reservedList');

            if (results) results.classList.remove('d-none');
            if (availList) availList.innerHTML = '';
            if (resList) resList.innerHTML = '';

            if (!data.available || data.available.length === 0) {
                if (availList) availList.innerHTML = '<span class="text-muted small fw-semibold">No vehicles available on this date.</span>';
            } else {
                data.available.forEach(v => {
                    if (availList) {
                        availList.innerHTML += `<div class="badge bg-success text-white px-3 py-2 me-1 mb-1 rounded-3 shadow-sm d-inline-flex align-items-center"><i class="bi bi-ev-front-fill me-1"></i> ${v.license_plate} (${v.make} ${v.model})</div> `;
                    }
                });
            }

            if (!data.reserved || data.reserved.length === 0) {
                if (resList) resList.innerHTML = '<span class="text-muted small fw-semibold">No vehicles reserved on this date.</span>';
            } else {
                data.reserved.forEach(v => {
                    if (resList) {
                        resList.innerHTML += `<div class="badge bg-danger text-white px-3 py-2 me-1 mb-1 rounded-3 shadow-sm d-inline-flex align-items-center"><i class="bi bi-x-circle-fill me-1"></i> ${v.license_plate} (${v.make} ${v.model})</div> `;
                    }
                });
            }

            // Sync FullCalendar live to date
            if (reservationCalendarInstance && data.date) {
                reservationCalendarInstance.gotoDate(data.date);
            }
        })
        .catch(err => {
            console.error('Availability check failed:', err);
            alert('Could not fetch schedule slot availability. Please try again.');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
}

function filterReservationsTable() {
    const input = document.getElementById('reservationSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? '' : 'none';
    });
}

function initScheduleCalendar() {
    const calendarEl = document.getElementById('reservationCalendar');
    if (calendarEl && typeof FullCalendar !== 'undefined') {
        const eventsData = @json($calendarEvents ?? []);

        reservationCalendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            themeSystem: 'bootstrap5',
            events: eventsData,
            dateClick: function(info) {
                const checkInput = document.getElementById('checkDateInput');
                if (checkInput) {
                    checkInput.value = info.dateStr;
                    checkAvailability();
                }
            }
        });
        reservationCalendarInstance.render();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScheduleCalendar);
} else {
    initScheduleCalendar();
}
</script>

@endsection
