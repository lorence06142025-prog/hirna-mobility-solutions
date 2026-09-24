<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hirna Mobility Solutions - Fleet & Transportation Management System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet Interactive GPS Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Custom Instant SPA Tab Switcher Animation Styles -->
    <style>
        #mainContentBody {
            transition: opacity 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .tab-fade-out {
            opacity: 0;
        }
        .tab-fade-in {
            opacity: 1;
        }
        #tabLoadingProgress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #10B981 0%, #06B6D4 100%);
            z-index: 99999;
            width: 0%;
            transition: width 0.2s ease;
        }
    </style>
</head>
<body>

    <!-- Top Loading Bar for SPA Tab Transitions -->
    <div id="tabLoadingProgress"></div>

    <!-- Mobile Navigation Offcanvas Drawer (Only visible on small screens) -->
    <style>
        :root {
            --primary: #CE2029;
            --primary-hover: #B91C1C;
            --secondary: #7F1D1D;
            --accent: #F59E0B;
            --background: #FFF8F8;
            --card-bg: #FFFFFF;
            --border-color: #FEE2E2;
            --text-dark: #4C0519;
            --text-light: #991B1B;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--background);
            color: var(--text-dark);
            overflow-x: hidden;
            letter-spacing: -0.01em;
        }

        /* Desktop Sidebar - Hirna Crimson Gradient */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #CE2029 0%, #7F1D1D 100%);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            padding-top: 1.25rem;
            color: white;
            box-shadow: 4px 0 24px rgba(206, 32, 41, 0.25);
            transition: all 0.3s ease;
        }

        .sidebar .brand {
            padding: 0 1.25rem 1.25rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 1rem;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            margin: 0.15rem 0.85rem;
            color: #FFDDA1;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.925rem;
            border-radius: 10px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-nav-link:hover {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(2px);
        }

        .sidebar-nav-link.active {
            color: #7F1D1D;
            background: #F59E0B;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.5);
        }

        .sidebar-nav-link i {
            font-size: 1.15rem;
            margin-right: 0.75rem;
        }

        /* Top Navigation Header */
        .top-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0;
            margin-bottom: 1.75rem;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        /* Main Content Container */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem 2.5rem;
            min-height: 100vh;
            transition: margin 0.3s ease, padding 0.3s ease;
        }

        /* Card Styling */
        .premium-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color) !important;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 6px 16px -4px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .main-content > .row .premium-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
        }

        /* Prevent transform flickering inside modals */
        .modal .premium-card,
        .modal .card {
            transform: none !important;
            transition: none !important;
        }

        /* Tables & Responsive Wrappers */
        .table {
            --bs-table-bg: transparent;
            font-size: 0.9rem;
        }

        .table thead th {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            border-bottom: 1px solid var(--border-color);
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
            white-space: nowrap;
        }

        .table-responsive {
            -webkit-overflow-scrolling: touch;
            border-radius: 10px;
        }

        /* Typography */
        .page-header-title {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            margin-bottom: 0.2rem;
        }

        .page-header-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        /* Buttons & Badges */
        .btn-premium {
            background-color: var(--primary);
            color: white;
            border-radius: 8px;
            padding: 0.55rem 1.2rem;
            font-weight: 500;
            font-size: 0.9rem;
            border: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }

        .btn-premium:hover {
            background-color: var(--primary-hover);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        /* Pulse Indicator */
        .loader-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10B981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 1.6s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* ========================================================
           RESPONSIVE MEDIA QUERIES FOR MOBILE & TABLET DEVICES
           ======================================================== */
        @media (max-width: 991.98px) {
            .sidebar {
                display: none; /* Hide fixed sidebar on mobile */
            }

            .main-content {
                margin-left: 0 !important;
                padding: 1.25rem 1rem !important;
            }

            .top-navbar {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.75rem;
                padding-bottom: 0.75rem;
            }

            .mobile-header-bar {
                display: flex !important;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                background: linear-gradient(135deg, #CE2029 0%, #7F1D1D 100%);
                padding: 0.85rem 1.25rem;
                border-radius: 14px;
                margin-bottom: 1.25rem;
                color: white;
                box-shadow: 0 6px 20px rgba(206, 32, 41, 0.35);
            }

            .page-header-title {
                font-size: 1.25rem;
            }
        }

        @media (min-width: 992px) {
            .mobile-header-bar {
                display: none !important;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Desktop Sidebar Navigation -->
    <div class="sidebar">
        <div class="brand d-flex align-items-center">
            <img src="{{ asset('images/hirna_logo.jpg') }}" alt="Hirna Logo" style="width: 42px; height: 42px; object-fit: cover; border-radius: 8px; border: 2px solid #F59E0B;" class="me-2 shadow-sm">
            <div>
                <span class="fs-5 fw-bold d-block text-white" style="letter-spacing: 0.5px;">HIRNA</span>
                <small class="fw-bold" style="font-size: 10px; letter-spacing: 1px; color: #FBBF24;">MOBILITY SOLUTIONS</small>
            </div>
        </div>

        @php
            $userRole = session('user_role', 'admin');
            
            // Server-validated Role Module Access Permissions
            $canAccessFleet = in_array($userRole, ['admin', 'fleet_manager']);
            $canAccessReservations = in_array($userRole, ['admin', 'fleet_manager', 'dispatcher', 'operations']);
            $canAccessTrips = in_array($userRole, ['admin', 'fleet_manager', 'dispatcher', 'finance', 'operations', 'driver']);
            $canAccessFuel = in_array($userRole, ['admin', 'fleet_manager', 'finance']);
            $canAccessCostAnalysis = in_array($userRole, ['admin', 'finance', 'operations']);
            $canAccessRoutes = in_array($userRole, ['admin', 'fleet_manager', 'dispatcher', 'operations']);
            $canAccessSecurity = ($userRole === 'admin');
        @endphp

        <nav class="mt-3">
            <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            @if($canAccessFleet)
            <a href="{{ route('vehicles.index') }}" class="sidebar-nav-link {{ Route::is('vehicles.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Fleet and Vehicle Management
            </a>
            <a href="{{ route('maintenance.index') }}" class="sidebar-nav-link {{ Route::is('maintenance.*') ? 'active' : '' }}">
                <i class="bi bi-wrench-adjustable"></i> Fleet Maintenance & Repairs (PMS)
            </a>
            @endif
            @if($canAccessReservations)
            <a href="{{ route('reservations.index') }}" class="sidebar-nav-link {{ Route::is('reservations.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> Vehicle Reservation and Dispatch
            </a>
            @endif
            @if($canAccessTrips)
            <a href="{{ route('trips.index') }}" class="sidebar-nav-link {{ Route::is('trips.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Driver and Trip Performance Monitoring
            </a>
            @endif
            @if($canAccessFuel)
            <a href="{{ route('fuel.index') }}" class="sidebar-nav-link {{ Route::is('fuel.*') ? 'active' : '' }}">
                <i class="bi bi-fuel-pump"></i> Fuel Management System
            </a>
            @endif
            @if($canAccessCostAnalysis)
            <a href="{{ route('cost-analysis.index') }}" class="sidebar-nav-link {{ Route::is('cost-analysis.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> Transport Cost Analysis
            </a>
            @endif
            @if($canAccessRoutes)
            <a href="{{ route('routes.index') }}" class="sidebar-nav-link {{ Route::is('routes.*') ? 'active' : '' }}">
                <i class="bi bi-compass"></i> Route Planning and Optimization
            </a>
            @endif
            @if($canAccessSecurity)
            <a href="{{ route('admin.security.index') }}" class="sidebar-nav-link {{ Route::is('admin.security.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i> Security & Access Center
            </a>
            @endif
        </nav>
    </div>

    <!-- Mobile Offcanvas Sidebar Drawer (For Mobile & Tablet screens) -->
    <div class="offcanvas offcanvas-start text-white" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="max-width: 290px; background: linear-gradient(180deg, #CE2029 0%, #7F1D1D 100%) !important; z-index: 1060;">
        <div class="offcanvas-header border-bottom border-white border-opacity-10 py-3">
            <div class="d-flex align-items-center">
                <img src="{{ asset('images/hirna_logo.jpg') }}" alt="Hirna Logo" style="width: 38px; height: 38px; object-fit: cover; border-radius: 8px; border: 2px solid #F59E0B;" class="me-2 shadow-sm">
                <div>
                    <span class="fs-6 fw-bold d-block text-white" style="letter-spacing: 0.5px;">HIRNA MOBILITY</span>
                    <small class="fw-bold text-warning" style="font-size: 10px; letter-spacing: 1px;">FLEET PORTAL</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 pt-3">
            <nav>
                <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
                @if($canAccessFleet)
                <a href="{{ route('vehicles.index') }}" class="sidebar-nav-link {{ Route::is('vehicles.*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i> Fleet and Vehicle Management
                </a>
                <a href="{{ route('maintenance.index') }}" class="sidebar-nav-link {{ Route::is('maintenance.*') ? 'active' : '' }}">
                    <i class="bi bi-wrench-adjustable"></i> Fleet Maintenance & Repairs (PMS)
                </a>
                @endif
                @if($canAccessReservations)
                <a href="{{ route('reservations.index') }}" class="sidebar-nav-link {{ Route::is('reservations.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i> Vehicle Reservation and Dispatch
                </a>
                @endif
                @if($canAccessTrips)
                <a href="{{ route('trips.index') }}" class="sidebar-nav-link {{ Route::is('trips.*') ? 'active' : '' }}">
                    <i class="bi bi-geo-alt"></i> Driver and Trip Performance Monitoring
                </a>
                @endif
                @if($canAccessFuel)
                <a href="{{ route('fuel.index') }}" class="sidebar-nav-link {{ Route::is('fuel.*') ? 'active' : '' }}">
                    <i class="bi bi-fuel-pump"></i> Fuel Management System
                </a>
                @endif
                @if($canAccessCostAnalysis)
                <a href="{{ route('cost-analysis.index') }}" class="sidebar-nav-link {{ Route::is('cost-analysis.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i> Transport Cost Analysis
                </a>
                @endif
                @if($canAccessRoutes)
                <a href="{{ route('routes.index') }}" class="sidebar-nav-link {{ Route::is('routes.*') ? 'active' : '' }}">
                    <i class="bi bi-compass"></i> Route Planning and Optimization
                </a>
                @endif
                @if($canAccessSecurity)
                <a href="{{ route('admin.security.index') }}" class="sidebar-nav-link {{ Route::is('admin.security.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock-fill"></i> Security & Access Center
                </a>
                @endif
            </nav>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">

        <!-- Mobile Top Header Bar (Shown only on mobile/tablets) -->
        <div class="mobile-header-bar">
            <div class="d-flex align-items-center">
                <button class="btn me-3 border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" style="background: #FFFFFF !important; border: 2px solid #F59E0B !important; width: 46px; height: 46px; z-index: 10;">
                    <div class="d-flex flex-column justify-content-between" style="width: 22px; height: 16px;">
                        <span style="display: block; width: 100%; height: 3px; background-color: #CE2029; border-radius: 2px;"></span>
                        <span style="display: block; width: 100%; height: 3px; background-color: #CE2029; border-radius: 2px;"></span>
                        <span style="display: block; width: 100%; height: 3px; background-color: #CE2029; border-radius: 2px;"></span>
                    </div>
                </button>
                <div>
                    <span class="fw-bold d-block text-white" style="font-size: 14px;">HIRNA MOBILITY</span>
                    <span class="badge bg-warning text-dark" style="font-size: 9px;">Taxi & Fleet Portal</span>
                </div>
            </div>
            <a href="tel:0288884476" class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-decoration-none text-danger">
                <i class="bi bi-telephone-fill me-1"></i> (02) 8888-HIRNA
            </a>
        </div>

        <!-- Enterprise Top Navigation Bar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <span class="loader-pulse me-2" style="background-color: #CE2029;"></span>
                <span class="small fw-semibold text-muted">Hirna Mobility Solutions &bull; Fleet Management & Transport Cost Analysis Portal</span>
            </div>
            <div class="d-flex align-items-center gap-3 ms-auto">
                @php
                    $currentRole = session('user_role', 'admin');
                    $currentName = session('user_name', 'Hirna System Admin');
                    $currentEmail = session('user_email', 'admin@hirna.ph');
                    
                    $authUser = \App\Models\User::where('email', \Illuminate\Support\Str::lower($currentEmail))->first();
                    $userAvatarUrl = $authUser ? $authUser->avatar_url : session('user_avatar', 'https://ui-avatars.com/api/?name='.urlencode($currentName).'&background=CE2029&color=ffffff&bold=true');

                    $roleTitles = [
                        'admin' => 'System Administrator',
                        'fleet_manager' => 'Fleet Manager',
                        'dispatcher' => 'Dispatcher',
                        'finance' => 'Finance Officer',
                        'operations' => 'Operations Manager',
                        'driver' => 'Hirna Driver',
                    ];
                    $activeRoleTitle = $roleTitles[$currentRole] ?? 'System Administrator';
                @endphp
                <!-- Dynamic User Profile & Live Role Switcher -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-3 px-3 py-1 fw-medium d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ $userAvatarUrl }}" alt="{{ $currentName }}" class="rounded-circle shadow-sm" style="width: 24px; height: 24px; object-fit: cover; border: 1.5px solid #F59E0B;">
                        <span>{{ $activeRoleTitle }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 small" style="min-width: 250px;">
                        <li>
                            <div class="px-3 py-2 border-bottom text-center bg-light rounded-top-3">
                                <img src="{{ $userAvatarUrl }}" alt="{{ $currentName }}" class="rounded-circle shadow-sm mb-2" style="width: 52px; height: 52px; object-fit: cover; border: 2px solid #F59E0B;">
                                <strong class="d-block text-dark">{{ $currentName }}</strong>
                                <small class="text-muted d-block" style="font-size: 11px;">{{ $currentEmail }}</small>
                                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 d-block mt-2 py-1"><i class="bi bi-shield-check me-1"></i> Role: {{ $activeRoleTitle }}</span>
                            </div>
                        </li>
                        <li class="p-1">
                            <a href="{{ route('profile.index') }}" class="dropdown-item fw-medium rounded-2 py-2">
                                <i class="bi bi-person-gear me-2 text-danger"></i> My Profile & Account Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="p-1">
                            <button type="button" class="dropdown-item text-danger fw-semibold rounded-2 py-2" data-bs-toggle="modal" data-bs-target="#logoutConfirmationModal">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Logout Confirmation Modal -->
        <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 bg-danger text-white rounded-top-4">
                        <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-right me-2"></i> Confirm Logout</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle d-inline-flex mb-3">
                            <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Are you sure you want to log out?</h6>
                        <p class="text-muted small mb-0">Your active session will be ended, and you will return to the sign-in portal.</p>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">
                                <i class="bi bi-box-arrow-right me-1"></i> Yes, Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast notifications -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div id="mainContentBody" class="tab-fade-in">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js (for analytics rendering) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Leaflet Interactive GPS Map Engine JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    @yield('scripts')
</body>
</html>
