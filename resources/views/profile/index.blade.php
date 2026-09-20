@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-danger text-white px-3 py-1 rounded-pill" style="font-size: 11px; letter-spacing: 0.5px; background: #CE2029 !important;">HIRNA MOBILITY SOLUTIONS INC.</span>
            <span class="text-muted" style="font-size: 12px;"><i class="bi bi-person-badge-fill text-danger me-1"></i> User Account & Security Settings</span>
        </div>
        <h2 class="page-header-title mt-1">My Account Profile & Security Settings</h2>
        <p class="page-header-subtitle">Update your personal details, upload a custom profile avatar picture, change your password, and inspect security access permissions.</p>
    </div>
</div>

<!-- Flash Notifications -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4 bg-success bg-opacity-10 text-success fw-medium" role="alert">
        <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4 bg-danger bg-opacity-10 text-danger fw-medium" role="alert">
        <i class="bi bi-exclamation-triangle-fill text-danger fs-5 me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 bg-danger bg-opacity-10 text-danger p-3" role="alert">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Profile Setting Validation Error:</div>
        <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Profile Overview Header Card -->
<div class="card premium-card p-4 mb-4 border-0 shadow-sm">
    <div class="d-flex align-items-center flex-wrap gap-4">
        <!-- Avatar Preview Container -->
        <div class="position-relative">
            <img id="avatarImagePreview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                 class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover; border: 4px solid #F59E0B;">
            
            <button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute bottom-0 end-0 p-2 shadow" 
                    data-bs-toggle="modal" data-bs-target="#changeAvatarModal" title="Upload New Profile Picture" 
                    style="background: #CE2029 !important; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-camera-fill text-white" style="font-size: 13px;"></i>
            </button>
        </div>

        <div>
            <h4 class="fw-bold mb-1 text-dark">{{ $user->name }}</h4>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                <span class="badge bg-secondary" style="font-size: 11px;">
                    <i class="bi bi-envelope-fill me-1"></i> {{ $user->email }}
                </span>
                @if(!empty($user->phone_number))
                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25" style="font-size: 11px;">
                        <i class="bi bi-telephone-fill me-1"></i> {{ $user->phone_number }}
                    </span>
                @endif
                <span class="badge bg-danger text-white px-3 py-1 rounded-pill" style="font-size: 11px; background: #CE2029 !important;">
                    <i class="bi bi-shield-check me-1"></i> {{ ucwords(str_replace('_', ' ', $user->role ?? 'User')) }}
                </span>
            </div>
            <span class="text-muted small d-block">
                <i class="bi bi-briefcase-fill me-1 text-warning"></i> Designation: 
                <strong>{{ $user->job_title ?: 'System Administrator' }}</strong> &bull; Member since {{ $user->created_at ? $user->created_at->format('M Y') : '2026' }}
            </span>
        </div>

        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-outline-danger rounded-3 px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#changeAvatarModal">
                <i class="bi bi-upload me-1"></i> Change Avatar
            </button>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Personal Information Settings -->
    <div class="col-lg-7">
        <div class="card premium-card p-4 h-100 border-0 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-person-lines-fill text-danger me-2"></i> Personal Information</h5>
            
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-dark">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control rounded-3" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-dark">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control rounded-3" required>
                        <small class="text-muted" style="font-size: 10px;">Used for sign-in and security notifications.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-dark">Contact Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" placeholder="+63 917 123 4567" class="form-control rounded-3">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-dark">Job Title / Designation</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" placeholder="e.g. Fleet Operations Officer" class="form-control rounded-3">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small text-dark">Assigned System Role</label>
                        <input type="text" value="{{ ucwords(str_replace('_', ' ', $user->role ?? 'User')) }}" class="form-control rounded-3 bg-light" disabled readonly>
                        <small class="text-muted" style="font-size: 10.5px;">System roles are managed by Superadmin in Security Access Center.</small>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold" style="background: #CE2029 !important;">
                        <i class="bi bi-floppy-fill me-1"></i> Save Profile Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Security & Password Change Form -->
    <div class="col-lg-5">
        <div class="card premium-card p-4 h-100 border-0 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Security & Password Change</h5>
            
            <form action="{{ route('profile.update-password') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Current Password</label>
                    <div class="input-group">
                        <input type="password" id="current_password" name="current_password" class="form-control rounded-start-3" required>
                        <button class="btn btn-outline-secondary rounded-end-3" type="button" onclick="togglePasswordVisibility('current_password', 'eye1')">
                            <i class="bi bi-eye-fill" id="eye1"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">New Password</label>
                    <div class="input-group">
                        <input type="password" id="new_password" name="new_password" class="form-control rounded-start-3" onkeyup="checkPasswordStrength(this.value);" required>
                        <button class="btn btn-outline-secondary rounded-end-3" type="button" onclick="togglePasswordVisibility('new_password', 'eye2')">
                            <i class="bi bi-eye-fill" id="eye2"></i>
                        </button>
                    </div>
                    <div class="progress mt-2" style="height: 5px;">
                        <div id="passwordStrengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small id="passwordStrengthText" class="text-muted d-block mt-1" style="font-size: 10px;">Min 8 chars: 1 Uppercase, 1 Number, 1 Special Char (@$!%).</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control rounded-start-3" required>
                        <button class="btn btn-outline-secondary rounded-end-3" type="button" onclick="togglePasswordVisibility('new_password_confirmation', 'eye3')">
                            <i class="bi bi-eye-fill" id="eye3"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-dark rounded-3 px-4 fw-bold">
                        <i class="bi bi-key-fill me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Profile Picture Modal -->
<div class="modal fade" id="changeAvatarModal" tabindex="-1" aria-labelledby="changeAvatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered rounded-4 overflow-hidden">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0" style="background: linear-gradient(135deg, #CE2029 0%, #7F1D1D 100%);">
                <h5 class="modal-title fw-bold" id="changeAvatarModalLabel"><i class="bi bi-camera me-2"></i> Upload Profile Picture</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('profile.update-avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <img id="modalAvatarPreview" src="{{ $user->avatar_url }}" alt="Avatar Preview" class="rounded-circle shadow mb-3" style="width: 110px; height: 110px; object-fit: cover; border: 4px solid #F59E0B;">
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small text-dark">Select Image File (Max 2MB)</label>
                        <input type="file" name="avatar" class="form-control rounded-3" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" onchange="previewSelectedAvatar(this);" required>
                        <small class="text-muted mt-1 d-block" style="font-size: 11px;">Accepted formats: JPEG, PNG, WEBP, GIF. Image will be formatted automatically.</small>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-3 bg-light d-flex justify-content-between">
                    @if($user->avatar_path)
                        <form action="{{ route('profile.remove-avatar') }}" method="POST" class="d-inline" onsubmit="return confirm('Reset profile picture to default?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary rounded-3 btn-sm">
                                <i class="bi bi-trash me-1"></i> Reset Default
                            </button>
                        </form>
                    @else
                        <div></div>
                    @endif

                    <div>
                        <button type="button" class="btn btn-outline-secondary rounded-3 me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-3 fw-bold" style="background: #CE2029 !important;">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Picture
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePasswordVisibility(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('bi-eye-fill');
        eye.classList.add('bi-eye-slash-fill');
    } else {
        input.type = 'password';
        eye.classList.remove('bi-eye-slash-fill');
        eye.classList.add('bi-eye-fill');
    }
}

function previewSelectedAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('modalAvatarPreview').src = e.target.result;
            document.getElementById('avatarImagePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function checkPasswordStrength(val) {
    const bar = document.getElementById('passwordStrengthBar');
    const text = document.getElementById('passwordStrengthText');
    let strength = 0;

    if (val.length >= 8) strength += 25;
    if (val.match(/[A-Z]/)) strength += 25;
    if (val.match(/[0-9]/)) strength += 25;
    if (val.match(/[@$!%*#?&~^()_+\-=\[\]{};\':"\\\\|,.<>\/?]/)) strength += 25;

    bar.style.width = strength + '%';
    if (strength <= 25) {
        bar.className = 'progress-bar bg-danger';
        text.innerHTML = '<span class="text-danger fw-bold">Weak Password</span> (Must add uppercase, digits, and special chars)';
    } else if (strength <= 75) {
        bar.className = 'progress-bar bg-warning';
        text.innerHTML = '<span class="text-warning fw-bold">Medium Strength</span> (Add special symbols for high security)';
    } else {
        bar.className = 'progress-bar bg-success';
        text.innerHTML = '<span class="text-success fw-bold">Strong Password</span> (Complies with ISO 25010 security standards)';
    }
}
</script>
@endsection
