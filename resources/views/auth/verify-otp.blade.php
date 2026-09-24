<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hirna Mobility Solutions - Security OTP Verification</title>
    
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

        .otp-wrapper {
            width: 100%;
            max-width: 480px;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 40px rgba(206, 32, 41, 0.25);
            padding: 2.5rem 2rem;
            z-index: 1;
        }

        .otp-input {
            width: 52px;
            height: 60px;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            background: rgba(30, 41, 59, 0.8) !important;
            border: 2px solid rgba(255, 255, 255, 0.15) !important;
            color: #F8FAFC !important;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .otp-input:focus {
            border-color: #F59E0B !important;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.25) !important;
            background: rgba(30, 41, 59, 1) !important;
        }

        .btn-verify {
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

        .btn-verify:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #4C0519;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
        }

        .badge-security {
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #FDE047;
            padding: 0.4rem 0.9rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="ambient-orb-1"></div>
    <div class="ambient-orb-2"></div>

    <div class="otp-wrapper text-center">
        <!-- Logo & Branding -->
        <div class="d-flex justify-content-center align-items-center mb-3">
            <img src="{{ asset('images/hirna_logo.jpg') }}" alt="Hirna Logo" style="width: 48px; height: 48px; object-fit: cover; border-radius: 12px; border: 2px solid #F59E0B;" class="me-2 shadow">
            <div class="text-start">
                <span class="fs-4 fw-bold text-white d-block style='line-height:1.1;'">HIRNA MOBILITY</span>
                <small class="fw-bold text-warning" style="font-size: 10px; letter-spacing: 1px;">2-FACTOR SECURITY</small>
            </div>
        </div>

        <div class="badge-security d-inline-flex align-items-center gap-2 mb-3">
            <i class="bi bi-shield-lock-fill text-warning fs-6"></i>
            TWO-FACTOR AUTHENTICATION
        </div>

        <h4 class="fw-bold text-white mb-2">Check Your Email</h4>
        <p class="text-white-50 small mb-4">
            We sent a 6-digit verification code to:<br>
            <strong class="text-warning fs-6">{{ session('otp_target_email', 'your email') }}</strong>
        </p>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 small mb-3 bg-success bg-opacity-25 text-white fw-medium shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 small mb-3 bg-danger bg-opacity-25 text-white fw-medium shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('otp.verify') }}" method="POST" id="otpForm">
            @csrf
            <input type="hidden" name="otp" id="fullOtpInput">

            <!-- 6 Digit Inputs -->
            <div class="d-flex justify-content-center gap-2 mb-4">
                <input type="text" class="form-control otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus required autocomplete="off">
                <input type="text" class="form-control otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required autocomplete="off">
                <input type="text" class="form-control otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required autocomplete="off">
                <input type="text" class="form-control otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required autocomplete="off">
                <input type="text" class="form-control otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required autocomplete="off">
                <input type="text" class="form-control otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required autocomplete="off">
            </div>

            <button type="submit" class="btn btn-verify mb-3 py-3 fw-bold">
                <i class="bi bi-shield-check me-2"></i> Verify Code & Continue
            </button>
        </form>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-30 text-white-50 small">
            <form action="{{ route('otp.resend') }}" method="POST" class="d-inline" id="resendForm">
                @csrf
                <button type="submit" id="resendBtn" class="btn btn-link p-0 text-white-50 text-decoration-none small fw-semibold" disabled>
                    <i class="bi bi-clock-history me-1"></i> Resend Code in (60s)
                </button>
            </form>

            <a href="{{ route('login') }}" class="text-white-50 text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('otpForm');
        const fullOtpInput = document.getElementById('fullOtpInput');
        const resendBtn = document.getElementById('resendBtn');
        const resendAvailableAt = {{ session('otp_resend_available_at', now()->addSeconds(60)->timestamp) }};

        // 60-Second Resend Cooldown Countdown Timer
        function updateResendTimer() {
            const nowSec = Math.floor(Date.now() / 1000);
            const secondsLeft = Math.max(0, resendAvailableAt - nowSec);

            if (secondsLeft > 0) {
                resendBtn.disabled = true;
                resendBtn.classList.add('text-white-50');
                resendBtn.classList.remove('text-warning');
                resendBtn.style.cursor = 'not-allowed';
                resendBtn.innerHTML = `<i class="bi bi-clock-history me-1"></i> Resend Code in (${secondsLeft}s)`;
                setTimeout(updateResendTimer, 1000);
            } else {
                resendBtn.disabled = false;
                resendBtn.classList.remove('text-white-50');
                resendBtn.classList.add('text-warning');
                resendBtn.style.cursor = 'pointer';
                resendBtn.innerHTML = `<i class="bi bi-arrow-clockwise me-1"></i> Resend OTP Code`;
            }
        }

        if (resendBtn) {
            updateResendTimer();
        }

        inputs.forEach((input, index) => {
            // Handle single character typing & auto focus
            input.addEventListener('input', function(e) {
                const val = e.target.value;
                if (val.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                combineOtp();
            });

            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text').trim();
                if (/^\d{6}$/.test(pastedData)) {
                    pastedData.split('').forEach((char, i) => {
                        if (inputs[i]) inputs[i].value = char;
                    });
                    combineOtp();
                    inputs[5].focus();
                }
            });
        });

        function combineOtp() {
            let code = '';
            inputs.forEach(input => code += input.value);
            fullOtpInput.value = code;
        }

        form.addEventListener('submit', function(e) {
            combineOtp();
            if (fullOtpInput.value.length !== 6) {
                e.preventDefault();
                alert('Please enter all 6 digits of your verification code.');
            }
        });
    });
    </script>
</body>
</html>
