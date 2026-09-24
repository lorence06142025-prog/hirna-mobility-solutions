@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge text-white px-3 py-1 rounded-pill" style="font-size: 11px; letter-spacing: 0.5px; background: #7F1D1D !important;">SUPERADMIN SECURITY CENTER</span>
            <span class="text-muted" style="font-size: 12px;"><i class="bi bi-shield-lock-fill text-danger me-1"></i> Native ISO 25010 System Security</span>
        </div>
        <h2 class="page-header-title mt-1">Superadmin Security & User Access Control Center</h2>
        <p class="page-header-subtitle">Monitor brute-force rate-limiting, unlock locked users, create system accounts, and inspect security audit logs.</p>
    </div>
    <div class="col-auto d-flex gap-2 flex-wrap">
        <button class="btn btn-success rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-person-plus-fill me-1"></i> Create New User
        </button>
        <button class="btn btn-danger rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#quickUnlockModal" style="background: #CE2029 !important;">
            <i class="bi bi-unlock-fill me-1"></i> Unlock Account / IP
        </button>
        <form action="{{ route('admin.security.clear-logs') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear security audit logs?');">
            @csrf
            <button type="submit" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-trash me-1"></i> Clear Audit Logs
            </button>
        </form>
    </div>
</div>

<!-- Security Flash Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4 bg-success bg-opacity-10 text-success fw-medium" role="alert">
        <i class="bi bi-shield-check text-success fs-5 me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4 bg-danger bg-opacity-10 text-danger fw-medium" role="alert">
        <i class="bi bi-exclamation-triangle-fill text-danger fs-5 me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 bg-danger bg-opacity-10 text-danger p-3" role="alert">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> User Creation Error:</div>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Inter-System Security Status Banner -->
<div class="alert alert-dark bg-dark text-white border-0 rounded-4 p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-shield-fill-check text-success fs-3 me-3"></i>
            <div>
                <span class="fw-bold d-block text-white small">NATIVE SECURITY DEFENSE SYSTEM ACTIVE</span>
                <span class="text-white-50 fw-medium" style="font-size: 11px;">Protected by RateLimiter brute-force blocks (3 attempts), anti-bot honeypot, HTTP security headers, and Bcrypt hashing.</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-success text-white fw-bold px-3 py-2"><i class="bi bi-check-circle me-1"></i> Rate Limiter: Active</span>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2"><i class="bi bi-robot me-1"></i> Honeypot: Enabled</span>
            <span class="badge bg-info text-dark fw-bold px-3 py-2"><i class="bi bi-lock-fill me-1"></i> Headers: Enforced</span>
        </div>
    </div>
</div>

<!-- 4 Key Security Overview Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card premium-card p-3 h-100 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3 me-2">
                    <i class="bi bi-shield-exclamation fs-4"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark small">Failed Login Strikes</span>
                    <small class="text-muted" style="font-size: 10px;">Password Failures</small>
                </div>
            </div>
            <h3 class="fw-bold text-warning mb-1">{{ number_format($totalFailedAttempts) }}</h3>
            <small class="text-muted" style="font-size: 11px;">Logged by RateLimiter</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card premium-card p-3 h-100 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3 me-2">
                    <i class="bi bi-lock-fill fs-4"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark small">Account Lockouts</span>
                    <small class="text-muted" style="font-size: 10px;">Brute-Force Triggers</small>
                </div>
            </div>
            <h3 class="fw-bold text-danger mb-1">{{ number_format($totalLockouts) }}</h3>
            <small class="text-muted" style="font-size: 11px;">Max 3 Strikes / 60s Block</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card premium-card p-3 h-100 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-dark bg-opacity-10 text-dark p-2 rounded-3 me-2">
                    <i class="bi bi-robot fs-4"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark small">Bot Honeypot Traps</span>
                    <small class="text-muted" style="font-size: 10px;">Automated Scrapers</small>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ number_format($totalHoneypotBlocks) }}</h3>
            <small class="text-muted" style="font-size: 11px;">Silently Blocked</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card premium-card p-3 h-100 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center mb-2">
                <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 me-2">
                    <i class="bi bi-person-check-fill fs-4"></i>
                </div>
                <div>
                    <span class="fw-bold d-block text-dark small">Registered Users</span>
                    <small class="text-muted" style="font-size: 10px;">System Accounts</small>
                </div>
            </div>
            <h3 class="fw-bold text-success mb-1">{{ number_format(count($users)) }}</h3>
            <small class="text-muted" style="font-size: 11px;">Active Roles & Accounts</small>
        </div>
    </div>
</div>

<!-- Lockout Management & Live Audit Trail Table Container -->
<div class="row g-4 mb-4">
    <!-- User Roles & Accounts Roster Panel -->
    <div class="col-lg-4">
        <div class="card premium-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-danger me-2"></i> User Roster & Status</h5>
                <button class="btn btn-sm btn-outline-success rounded-3" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="bi bi-plus-circle me-1"></i> Add
                </button>
            </div>

            <!-- Active Lockout Notice -->
            @if($lockedUsersCount > 0)
                <div class="alert alert-danger bg-danger bg-opacity-15 border border-danger border-opacity-30 rounded-3 p-3 mb-3">
                    <div class="d-flex align-items-center text-danger fw-bold mb-1">
                        <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i> {{ $lockedUsersCount }} Account(s) Currently Locked Out!
                    </div>
                    <small class="text-dark d-block">System rate limiter has blocked login attempts due to 3 failed password strikes.</small>
                </div>
            @endif

            <div class="list-group list-group-flush" style="font-size: 12.5px; max-height: 480px; overflow-y: auto;">
                @foreach($users as $usr)
                    <div class="list-group-item px-0 py-2 border-0 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $usr->avatar_url ?? ('https://ui-avatars.com/api/?name=' . urlencode($usr->name) . '&background=CE2029&color=fff&size=128') }}" 
                                     alt="{{ $usr->name }}" 
                                     class="rounded-circle border border-2 border-danger shadow-sm flex-shrink-0" 
                                     style="width: 36px; height: 36px; object-fit: cover;">
                                <div>
                                    <strong class="text-dark d-block" style="font-size: 13px; line-height: 1.2;">{{ $usr->name }}</strong>
                                    @if(!empty($usr->job_title))
                                        <small class="text-primary fw-medium d-block" style="font-size: 11px;">{{ $usr->job_title }}</small>
                                    @endif
                                    <small class="text-muted d-block" style="font-size: 11px;">{{ $usr->email }}</small>
                                    @if(!empty($usr->phone_number))
                                        <small class="text-success fw-medium d-block" style="font-size: 10.5px;"><i class="bi bi-telephone-fill me-1"></i> {{ $usr->phone_number }}</small>
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-dark" style="font-size: 10px;">{{ ucfirst($usr->role ?? 'User') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            @if($usr->is_locked)
                                <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 10px;">
                                    <i class="bi bi-lock-fill me-1"></i> LOCKED OUT (3/3)
                                </span>
                                <form action="{{ route('admin.security.unlock') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ $usr->email }}">
                                    <button type="submit" class="btn btn-sm btn-danger fw-bold rounded-3 px-3 py-1" style="font-size: 11px; background: #CE2029 !important;">
                                        🔓 Unlock User
                                    </button>
                                </form>
                            @elseif(($usr->attempts_count ?? 0) > 0)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 10px;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $usr->attempts_count }}/3 Strikes
                                </span>
                                <form action="{{ route('admin.security.unlock') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ $usr->email }}">
                                    <button type="submit" class="btn btn-xs btn-outline-warning text-dark fw-bold rounded-2 px-2 py-0" style="font-size: 10px;">
                                        Reset Strikes
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 10px;">
                                    <i class="bi bi-shield-check me-1"></i> Active / Clean
                                </span>
                                <span class="text-muted small" style="font-size: 10px;">No Lockout</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Live Security Audit Log Table -->
    <div class="col-lg-8">
        <div class="card premium-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0"><i class="bi bi-journal-text text-danger me-2"></i> Security Incident Audit Log</h5>
                <span class="badge bg-dark text-white rounded-pill px-3 py-2">{{ $securityLogs->total() }} Total Incidents</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted" style="font-size: 11px; font-weight: 700; text-transform: uppercase;">
                            <th>TIMESTAMP</th>
                            <th>SECURITY EVENT</th>
                            <th>TARGET ACCOUNT / EMAIL</th>
                            <th>CLIENT IP</th>
                            <th>DETAILS & REASON</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($securityLogs as $log)
                            <tr>
                                <td style="font-size: 11.5px; white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                                </td>
                                <td>
                                    @if($log->event_type === 'successful_login')
                                        <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 10px;"><i class="bi bi-check-circle me-1"></i> Success</span>
                                    @elseif($log->event_type === 'failed_login')
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 10px;"><i class="bi bi-exclamation-triangle me-1"></i> Password Fail</span>
                                    @elseif($log->event_type === 'account_lockout')
                                        <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 10px;"><i class="bi bi-lock-fill me-1"></i> Lockout</span>
                                    @elseif($log->event_type === 'admin_unlock')
                                        <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 10px;"><i class="bi bi-unlock-fill me-1"></i> Admin Unlock</span>
                                    @elseif($log->event_type === 'admin_create_user')
                                        <span class="badge bg-info text-dark rounded-pill px-2 py-1" style="font-size: 10px;"><i class="bi bi-person-plus me-1"></i> User Created</span>
                                    @elseif($log->event_type === 'bot_honeypot_blocked')
                                        <span class="badge bg-dark text-white rounded-pill px-2 py-1" style="font-size: 10px;"><i class="bi bi-robot me-1"></i> Bot Trapped</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill" style="font-size: 10px;">{{ $log->event_type }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-dark small d-block">{{ $log->email ?: 'N/A' }}</strong>
                                </td>
                                <td>
                                    <code class="bg-light text-danger px-2 py-1 rounded small" style="font-size: 11px;">{{ $log->ip_address ?: '127.0.0.1' }}</code>
                                </td>
                                <td style="font-size: 11.5px; max-width: 220px;">
                                    <span class="text-muted d-block text-truncate">{{ $log->details }}</span>
                                </td>
                                <td>
                                    @if($log->email)
                                        <form action="{{ route('admin.security.unlock') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="email" value="{{ $log->email }}">
                                            <input type="hidden" name="ip_address" value="{{ $log->ip_address }}">
                                            <button type="submit" class="btn btn-xs btn-outline-danger fw-bold rounded-2 px-2 py-1" style="font-size: 10.5px;">
                                                🔓 Unlock
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
                                    No security incidents logged yet. All system authentication attempts are clean.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($securityLogs->hasPages())
                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-3">
                    <small class="text-muted">Page {{ $securityLogs->currentPage() }} of {{ $securityLogs->lastPage() }}</small>
                    {{ $securityLogs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal 1: Create New User Account -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg rounded-4 overflow-hidden">
        <div class="modal-content border-0 shadow">
            <form id="createUserForm" action="{{ route('admin.security.users.store') }}" method="POST" novalidate>
                @csrf
                <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #10B981 0%, #064E3B 100%);">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i> Create New User Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 p-3 mb-3">
                            <div class="fw-bold text-danger mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Form Submission Failed! Please correct the errors below.
                            </div>
                            <ul class="mb-0 ps-3 small text-danger">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control rounded-3 @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" 
                               placeholder="e.g. Maria Clara Santos" 
                               required 
                               minlength="2" 
                               maxlength="50"
                               style="text-transform: capitalize;"
                               autocomplete="off">
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                            Min 2, max 50 chars. Auto-capitalized (e.g. Maria Clara Santos).
                        </small>
                        @error('name')
                            <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback fw-semibold" id="nameJsError">Full Name is required (2 to 50 characters).</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Job Title / Designation</label>
                        <input type="text" 
                               id="job_title" 
                               name="job_title" 
                               class="form-control rounded-3 @error('job_title') is-invalid @enderror" 
                               value="{{ old('job_title') }}" 
                               placeholder="e.g. Senior Fleet & Maintenance Supervisor" 
                               maxlength="100">
                        @error('job_title')
                            <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Username / Email Address <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="email" 
                                   name="email" 
                                   class="form-control rounded-3 @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" 
                                   placeholder="e.g. maria.santos@hirna.ph" 
                                   required 
                                   minlength="3" 
                                   maxlength="30" 
                                   autocomplete="off">
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                Min 3, max 30 chars. Letters, digits, <code>_</code>, <code>.</code>, <code>-</code>, <code>@</code> allowed (no spaces).
                            </small>
                            @error('email')
                                <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback fw-semibold" id="emailJsError">Must be 3-30 characters with no spaces or invalid symbols.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number (PH Mobile) <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="phone_number" 
                                   name="phone_number" 
                                   class="form-control rounded-3 @error('phone_number') is-invalid @enderror" 
                                   value="{{ old('phone_number') }}" 
                                   placeholder="e.g. 09171234567" 
                                   required 
                                   maxlength="11" 
                                   inputmode="numeric" 
                                   autocomplete="off">
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                Philippine mobile format: Exactly 11 digits starting with <code>09</code>.
                            </small>
                            @error('phone_number')
                                <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback fw-semibold" id="phoneJsError">Must be numbers only, exactly 11 digits starting with 09 (e.g. 09171234567).</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Initial Password <span class="text-danger">*</span></label>
                        <input type="text" 
                               id="password" 
                               name="password" 
                               class="form-control rounded-3 @error('password') is-invalid @enderror" 
                               value="{{ old('password', 'Password@123') }}" 
                               required 
                               minlength="8" 
                               maxlength="100">
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                            Must include 8+ chars, 1 Uppercase [A-Z], 1 Lowercase [a-z], 1 Number [0-9], and 1 Special Char.
                        </small>
                        @error('password')
                            <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback fw-semibold" id="passwordJsError">Password must be 8+ chars with uppercase, lowercase, digit, & special char.</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">System Role & Permissions <span class="text-danger">*</span></label>
                        <select id="role" name="role" class="form-select rounded-3 @error('role') is-invalid @enderror" required>
                            <option value="fleet_manager" {{ old('role', 'fleet_manager') === 'fleet_manager' ? 'selected' : '' }}>🚛 Fleet Manager (Fleet & PMS Controls)</option>
                            <option value="dispatcher" {{ old('role') === 'dispatcher' ? 'selected' : '' }}>📡 Dispatcher (Trip Scheduling & GPS Telematics)</option>
                            <option value="finance" {{ old('role') === 'finance' ? 'selected' : '' }}>💰 Finance Officer (Cost Per KM & Ledger Export)</option>
                            <option value="operations" {{ old('role') === 'operations' ? 'selected' : '' }}>⚡ Operations Manager (Depot Charging & Safety)</option>
                            <option value="driver" {{ old('role') === 'driver' ? 'selected' : '' }}>🚕 Field Driver (Driver Console)</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>👑 System Administrator (Superadmin Access)</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback fw-semibold d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-3 fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Save & Create User Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Quick Unlock Account / Reset Rate Limiter -->
<div class="modal fade" id="quickUnlockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered rounded-4 overflow-hidden">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.security.unlock') }}" method="POST">
                @csrf
                <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #CE2029 0%, #7F1D1D 100%);">
                    <h5 class="modal-title fw-bold"><i class="bi bi-unlock-fill me-2"></i> Superadmin Account Unlocker</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Select a user from the dropdown <strong>OR type any email address</strong> below to reset their brute-force rate-limiter strikes and restore immediate login access.
                    </p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select User from System Roster</label>
                        <select id="selectUnlockUser" class="form-select rounded-3" onchange="document.getElementById('manualEmailInput').value = this.value;">
                            <option value="" selected>-- Select User to Unlock --</option>
                            @foreach($users as $usr)
                                <option value="{{ $usr->email }}">{{ $usr->name }} ({{ $usr->email }} &bull; Role: {{ ucfirst($usr->role ?? 'User') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">OR Type Email Address directly</label>
                        <input type="email" id="manualEmailInput" name="email" class="form-control rounded-3" placeholder="e.g. fleetmanager@hirna.ph" value="fleetmanager@hirna.ph" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Client IP Address (Optional)</label>
                        <input type="text" name="ip_address" value="127.0.0.1" class="form-control rounded-3" placeholder="e.g. 127.0.0.1">
                        <small class="text-muted">Defaults to 127.0.0.1 if left blank.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4" style="background: #CE2029 !important;">
                        <i class="bi bi-unlock-fill me-1"></i> Unlock Account & Reset Rate Limit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-open modal if server-side validation failed
    @if($errors->any())
        var createUserModalEl = document.getElementById('createUserModal');
        if (createUserModalEl) {
            var modal = new bootstrap.Modal(createUserModalEl);
            modal.show();
        }
    @endif

    var form = document.getElementById('createUserForm');
    var phoneInput = document.getElementById('phone_number');
    var emailInput = document.getElementById('email');
    var nameInput = document.getElementById('name');
    var passwordInput = document.getElementById('password');

    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            // Strip non-numeric characters strictly
            this.value = this.value.replace(/[^0-9]/g, '');
            validatePhoneInput(this);
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', function () {
            validateEmailInput(this);
        });
    }

    if (nameInput) {
        nameInput.addEventListener('input', function () {
            // Real-time Title Case Auto-Capitalization for each word
            var cursorPos = this.selectionStart;
            this.value = this.value.replace(/\b[a-z]/g, function (char) {
                return char.toUpperCase();
            });
            this.setSelectionRange(cursorPos, cursorPos);
            validateNameInput(this);
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function () {
            validatePasswordInput(this);
        });
    }

    function validatePhoneInput(input) {
        var val = input.value.trim();
        var phoneErr = document.getElementById('phoneJsError');
        var regex = /^09\d{9}$/;

        if (!regex.test(val)) {
            input.classList.add('is-invalid');
            if (phoneErr) {
                if (val.length === 0) {
                    phoneErr.textContent = "Phone Number is required.";
                } else if (!val.startsWith("09")) {
                    phoneErr.textContent = "Phone Number must start with 09 (e.g. 09171234567).";
                } else if (val.length !== 11) {
                    phoneErr.textContent = "Phone Number must be exactly 11 digits (currently " + val.length + " digits).";
                } else {
                    phoneErr.textContent = "Phone Number must contain numbers only in 09XXXXXXXXX format.";
                }
                phoneErr.style.display = 'block';
            }
            return false;
        } else {
            input.classList.remove('is-invalid');
            if (phoneErr) phoneErr.style.display = 'none';
            return true;
        }
    }

    function validateEmailInput(input) {
        var val = input.value.trim();
        var emailErr = document.getElementById('emailJsError');
        // Min 3, Max 30 chars, allowed: a-z, A-Z, 0-9, _, ., -, @, no spaces
        var regex = /^[a-zA-Z0-9_.\-@]{3,30}$/;

        if (val.length < 3 || val.length > 30 || !regex.test(val)) {
            input.classList.add('is-invalid');
            if (emailErr) {
                if (val.length === 0) {
                    emailErr.textContent = "Username / Email Address is required.";
                } else if (val.length < 3) {
                    emailErr.textContent = "Username / Email Address must be at least 3 characters long.";
                } else if (val.length > 30) {
                    emailErr.textContent = "Username / Email Address must not exceed 30 characters (currently " + val.length + " chars).";
                } else {
                    emailErr.textContent = "Only letters, numbers, _, ., -, and @ allowed. Spaces are not allowed.";
                }
                emailErr.style.display = 'block';
            }
            return false;
        } else {
            input.classList.remove('is-invalid');
            if (emailErr) emailErr.style.display = 'none';
            return true;
        }
    }

    function validateNameInput(input) {
        var val = input.value.trim();
        var nameErr = document.getElementById('nameJsError');

        if (val.length < 2 || val.length > 50) {
            input.classList.add('is-invalid');
            if (nameErr) {
                if (val.length === 0) {
                    nameErr.textContent = "Full Name is required.";
                } else if (val.length < 2) {
                    nameErr.textContent = "Full Name must be at least 2 characters long.";
                } else {
                    nameErr.textContent = "Full Name must not exceed 50 characters (currently " + val.length + " chars).";
                }
                nameErr.style.display = 'block';
            }
            return false;
        } else {
            input.classList.remove('is-invalid');
            if (nameErr) nameErr.style.display = 'none';
            return true;
        }
    }

    function validatePasswordInput(input) {
        var val = input.value;
        var passErr = document.getElementById('passwordJsError');
        var hasUpper = /[A-Z]/.test(val);
        var hasLower = /[a-z]/.test(val);
        var hasDigit = /[0-9]/.test(val);
        var hasSpecial = /[@$!%*#?&~^()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(val);
        var isValid = val.length >= 8 && val.length <= 100 && hasUpper && hasLower && hasDigit && hasSpecial;

        if (!isValid) {
            input.classList.add('is-invalid');
            if (passErr) {
                passErr.textContent = "Password must be at least 8 chars with 1 uppercase, 1 lowercase, 1 digit, and 1 special character.";
                passErr.style.display = 'block';
            }
            return false;
        } else {
            input.classList.remove('is-invalid');
            if (passErr) passErr.style.display = 'none';
            return true;
        }
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            var isPhoneValid = validatePhoneInput(phoneInput);
            var isEmailValid = validateEmailInput(emailInput);
            var isNameValid = validateNameInput(nameInput);
            var isPassValid = validatePasswordInput(passwordInput);

            if (!isPhoneValid || !isEmailValid || !isNameValid || !isPassValid) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!isNameValid) nameInput.focus();
                else if (!isEmailValid) emailInput.focus();
                else if (!isPhoneValid) phoneInput.focus();
                else if (!isPassValid) passwordInput.focus();
            }
        });
    }
});
</script>
@endsection
