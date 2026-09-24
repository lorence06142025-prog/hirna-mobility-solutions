<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hirna Mobility Solutions - Enterprise Fleet Portal Login</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #CE2029;
            --primary-hover: #B91C1C;
            --accent: #F59E0B;
            --dark-bg: #4C0519;
            --card-bg: rgba(76, 5, 25, 0.85);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 15% 20%, #7F1D1D 0%, #4C0519 50%, #0F172A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #F8FAFC;
            position: relative;
            overflow-x: hidden;
        }

        /* Glowing Background Ambient Orbs */
        .ambient-orb-1 {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(206, 32, 41, 0.35) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
            pointer-events: none;
        }

        .ambient-orb-2 {
            position: absolute;
            bottom: -15%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.3) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 960px;
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 40px rgba(206, 32, 41, 0.25);
            overflow: hidden;
            z-index: 1;
        }

        /* Left Hero Showcase Section */
        .hero-section {
            background: linear-gradient(145deg, rgba(206, 32, 41, 0.75) 0%, rgba(127, 29, 29, 0.95) 100%);
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid rgba(255, 255, 255, 0.12);
            position: relative;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(245, 158, 11, 0.2);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #FDE047;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.825rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            width: fit-content;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            padding: 1.1rem;
            transition: transform 0.2s ease;
        }

        .stat-box:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.06);
        }

        /* Form Input Section */
        .form-section {
            padding: 3.5rem 3rem;
            background: rgba(15, 23, 42, 0.85);
        }

        .form-control {
            background: rgba(30, 41, 59, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #F8FAFC !important;
            border-radius: 12px;
            padding: 0.8rem 1.1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #CE2029 !important;
            box-shadow: 0 0 0 4px rgba(206, 32, 41, 0.2) !important;
        }

        .input-group-text {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #94A3B8 !important;
            border-radius: 12px 0 0 12px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #CE2029 0%, #B91C1C 100%);
            color: white;
            border: 1px solid #F59E0B;
            border-radius: 12px;
            padding: 0.85rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 16px rgba(206, 32, 41, 0.4);
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #4C0519;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
        }

        /* Role Quick Selection Pills */
        .role-pill {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #F8FAFC;
            padding: 0.45rem 0.85rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .role-pill:hover {
            background: #CE2029;
            border-color: #F59E0B;
            color: white;
            transform: translateY(-1px);
        }

        @media (max-width: 991.98px) {
            .hero-section {
                display: none;
            }
            .form-section {
                padding: 2.5rem 1.75rem;
            }
        }
    </style>
</head>
<body>

    <div class="ambient-orb-1"></div>
    <div class="ambient-orb-2"></div>

    <div class="login-wrapper">
        <div class="row g-0">
            
            <!-- Left Panel: Enterprise Branding & Live Specs -->
            <div class="col-lg-6 hero-section">
                <div>
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('images/hirna_logo.jpg') }}" alt="Hirna Logo" style="width: 52px; height: 52px; object-fit: cover; border-radius: 12px; border: 2px solid #F59E0B;" class="me-3 shadow">
                        <div>
                            <span class="fs-4 fw-bold text-white d-block" style="letter-spacing: 0.5px;">HIRNA MOBILITY</span>
                            <small class="fw-bold text-warning" style="font-size: 11px; letter-spacing: 1px;">SOLUTIONS INC.</small>
                        </div>
                    </div>

                    <div class="hero-badge mb-3">
                        <span class="spinner-grow spinner-grow-sm text-warning" role="status"></span>
                        OFFICIAL TNC & FLEET MANAGEMENT SYSTEM
                    </div>
                    
                    <h2 class="fw-extrabold text-white display-6 mb-3" style="letter-spacing: -0.03em;">
                        Fleet & Transportation Portal
                    </h2>

                    <!-- Fleet Specifications Grid -->
                    <div class="row g-3 mt-2">
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="d-flex align-items-center text-warning mb-1">
                                    <i class="bi bi-fuel-pump-fill fs-5 me-2"></i>
                                    <span class="fw-bold small text-white">Fuel Prediction</span>
                                </div>
                                <small class="text-white-50" style="font-size: 11px;">Gasoline (Gas), Diesel & EV</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="stat-box">
                                <div class="d-flex align-items-center text-warning mb-1">
                                    <i class="bi bi-cpu fs-5 me-2"></i>
                                    <span class="fw-bold small text-white">AI Predictive Engine</span>
                                </div>
                                <small class="text-white-50" style="font-size: 11px;">Machine Learning ML Model</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="stat-box">
                                <div class="d-flex align-items-center text-warning mb-1">
                                    <i class="bi bi-shield-check fs-5 me-2"></i>
                                    <span class="fw-bold small text-white">Telematics GPS</span>
                                </div>
                                <small class="text-white-50" style="font-size: 11px;">Eco-Safety & Driver Radar</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="stat-box">
                                <div class="d-flex align-items-center text-warning mb-1">
                                    <i class="bi bi-graph-up-arrow fs-5 me-2"></i>
                                    <span class="fw-bold small text-white">Cost Analytics</span>
                                </div>
                                <small class="text-white-50" style="font-size: 11px;">Cost-Per-KM & Audit Logs</small>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Right Panel: Modern Login Form -->
            <div class="col-lg-6 form-section d-flex flex-column justify-content-center">
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ asset('images/hirna_logo.jpg') }}" alt="Hirna Logo" style="width: 38px; height: 38px; object-fit: cover; border-radius: 8px; border: 2px solid #F59E0B;" class="me-2">
                        <span class="fs-3 fw-bold text-white">Sign In to Portal</span>
                    </div>
                </div>

                <!-- Validation Error Flash Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger border-0 rounded-3 small mb-3 bg-danger bg-opacity-25 text-white shadow-sm" role="alert">
                        <div class="d-flex align-items-center mb-1 fw-bold text-danger">
                            <i class="bi bi-shield-x me-2 fs-5"></i> Password Policy Violation
                        </div>
                        <ul class="mb-0 ps-3 small" style="font-size: 12px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 small mb-3 bg-success bg-opacity-25 text-white fw-medium shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill text-success me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 small mb-3 bg-danger bg-opacity-25 text-white fw-medium shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    
                    <!-- Native Anti-Bot Honeypot Trap (Invisible to humans, traps automated scrapers) -->
                    <input type="text" name="hirna_security_hp" style="display:none !important; position:absolute !important; left:-9999px !important;" tabindex="-1" autocomplete="off">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-white">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 text-white"><i class="bi bi-person-badge text-warning"></i></span>
                            <input type="text" id="emailInput" name="email" class="form-control border-start-0 text-white fw-medium" value="{{ old('email') }}" required placeholder="hirna admin" autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-white mb-0">Password</label>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text border-end-0 text-white"><i class="bi bi-lock text-warning"></i></span>
                            <input type="password" id="passwordInput" name="password" class="form-control border-start-0 border-end-0 text-white fw-medium" required placeholder="••••••••••••" autocomplete="current-password">
                            <button class="btn btn-outline-secondary border border-start-0 text-white" type="button" id="togglePassword">
                                <i class="bi bi-eye text-white" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit mb-3 fs-6 py-3 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Access Hirna Portal
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePassword && passwordInput && eyeIcon) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('bi-eye');
            eyeIcon.classList.toggle('bi-eye-slash');
        });
    }
    </script>
</body>
</html>
