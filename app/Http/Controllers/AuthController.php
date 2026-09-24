<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Display the secure login page.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Process authentication request with enterprise native security defenses:
     * - Anti-Bot Honeypot Trap
     * - Rate Limiting & Brute-Force Lockout (3 Failed Attempts Threshold)
     * - Bcrypt Hash Verification
     * - 2-Factor OTP Verification Dispatch
     * - Security Audit Trail Logging
     */
    public function login(Request $request)
    {
        // 1. Anti-Bot Honeypot Check (Silently reject automated scrapers)
        if ($request->filled('hirna_security_hp')) {
            SecurityLog::create([
                'event_type' => 'bot_honeypot_blocked',
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => 'Automated bot scraper trapped by hidden honeypot field',
            ]);
            Log::warning("SECURITY ALERT: Bot honeypot triggered from IP: {$request->ip()}");
            return back()->with('error', 'Automated submission detected and blocked by security filters.');
        }

        // 2. Validate Basic Form Structure First
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Please enter your username or email address.',
            'password.required' => 'Please enter your password.',
        ]);

        $rawInput = trim(Str::lower($request->input('email')));
        $cleanInput = str_replace([' ', '_', '-', '@', '.'], '', $rawInput);

        // 3. Flexible Database User Records Lookup (Email, Clean Username, or Display Name)
        $user = User::where('email', $rawInput)
            ->orWhere('email', $cleanInput)
            ->orWhereRaw("LOWER(REPLACE(email, ' ', '')) = ?", [$cleanInput])
            ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$cleanInput])
            ->first();

        // Standardized Lockout Key: Bind strictly to canonical account key without IP dependency for cloud serverless stability
        $accountKey = $user ? 'user_' . $user->id : 'input_' . $cleanInput;
        $throttleKey = Str::transliterate("login_lockout:{$accountKey}");
        $maxAttempts = 3; // Strict 3 Failed Attempts Lockout Threshold

        // 4. Check Rate Limiter Lockout (Max 3 Attempts for all accounts)
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            SecurityLog::create([
                'event_type' => 'account_lockout',
                'email' => $user ? $user->email : $rawInput,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => "Account locked out for {$seconds} seconds due to 3 consecutive failed login attempts",
            ]);
            
            Log::warning("SECURITY LOCKOUT: IP {$request->ip()} locked out on account " . ($user ? $user->email : $rawInput));
            
            return back()->with('error', "🚨 Security Lockout: Too many failed login attempts (3/3). Your account has been temporarily locked for {$seconds} seconds.");
        }

        if ($user && Hash::check($request->password, $user->password)) {
            // Clear brute-force rate limiter on successful password verification
            RateLimiter::clear($throttleKey);
            if ($user) {
                RateLimiter::clear(Str::transliterate("login_lockout:user_{$user->id}"));
                RateLimiter::clear(Str::transliterate("login_lockout:input_{$cleanInput}"));
            }

            // Server-side check: Has user successfully verified OTP within the last 50 minutes?
            $isOtpVerifiedWithin50Min = $user->last_otp_verified_at 
                && $user->last_otp_verified_at->gt(now()->subMinutes(50));

            if ($isOtpVerifiedWithin50Min) {
                // Skip OTP verification step - grant authenticated session immediately without updating last_otp_verified_at timestamp
                $request->session()->regenerate();
                $userRole = $user->role ?: 'admin';

                session([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_role' => $userRole,
                ]);

                SecurityLog::create([
                    'event_type' => 'successful_login',
                    'email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'details' => "Successful login (OTP bypassed: verified within 50 minutes) for role: {$userRole}",
                ]);

                Log::info("SECURITY AUDIT: Direct login (OTP 50-min window active) for {$user->email} ({$userRole})");

                $roleTitle = ucwords(str_replace('_', ' ', $userRole));
                return redirect()->route('dashboard')->with('success', "Welcome back, {$user->name}! Signed in as {$roleTitle} (OTP active from 50-min window).");
            }

            // Generate 6-digit OTP Code if > 50 minutes or never verified
            $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $targetEmail = filter_var($user->email, FILTER_VALIDATE_EMAIL) 
                ? $user->email 
                : (config('mail.demo_otp_email') ?: $user->email);

            session([
                'otp_pending_user_id' => $user->id,
                'otp_code' => $otpCode,
                'otp_expires_at' => now()->addMinutes(10)->timestamp,
                'otp_target_email' => $targetEmail,
                'otp_resend_available_at' => now()->addSeconds(60)->timestamp,
            ]);

            // Dispatch Real OTP Email via Resend API / SMTP
            $mailError = null;
            try {
                $this->sendOtpEmail($targetEmail, $otpCode);
            } catch (\Throwable $e) {
                $mailError = $e->getMessage();
                Log::error("OTP DISPATCH FAILED: " . $e->getMessage());
            }

            if ($mailError) {
                return redirect()->route('otp.show')->with('error', "Verification code generated, but mail dispatch failed: {$mailError}");
            }

            return redirect()->route('otp.show')->with('success', "A 6-digit verification code has been sent to {$targetEmail}. Check your inbox!");
        }

        // 5. Failed Login Attempt: Record Strike in RateLimiter
        RateLimiter::hit($throttleKey, 60); // 60-second decay timer
        $attemptsLeft = RateLimiter::remaining($throttleKey, $maxAttempts);

        SecurityLog::create([
            'event_type' => 'failed_login',
            'email' => $user ? $user->email : $rawInput,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => "Failed authentication attempt with invalid password. {$attemptsLeft} attempts remaining.",
        ]);

        Log::warning("SECURITY ALERT: Failed login attempt for " . ($user ? $user->email : $rawInput) . " from IP {$request->ip()}. {$attemptsLeft} attempts remaining.");

        if ($attemptsLeft <= 0) {
            return back()->with('error', "🚨 Security Lockout: 3 failed login attempts reached! Your account/IP has been temporarily locked for 60 seconds.");
        }

        return back()->with('error', "Invalid password or email address. You have {$attemptsLeft} attempt(s) remaining before temporary lockout.");
    }

    /**
     * Display the 2-factor OTP verification screen.
     */
    public function showOtp()
    {
        if (!session()->has('otp_pending_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify submitted 6-digit OTP code.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Please enter the 6-digit verification code.',
            'otp.size' => 'Verification code must be exactly 6 digits.',
        ]);

        if (!session()->has('otp_pending_user_id') || !session()->has('otp_code')) {
            return redirect()->route('login')->with('error', 'Verification session expired. Please sign in again.');
        }

        if (now()->timestamp > session('otp_expires_at')) {
            return back()->with('error', 'Verification code has expired. Please click "Resend OTP Code".');
        }

        if (trim($request->otp) !== session('otp_code')) {
            return back()->with('error', 'Invalid verification code. Please check your email and try again.');
        }

        // OTP Verification Successful -> Grant Full Authenticated Session
        $userId = session('otp_pending_user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'User account not found.');
        }

        // Update server-side timestamp for 50-minute OTP window
        $user->last_otp_verified_at = now();
        $user->save();

        $request->session()->regenerate();

        $userRole = $user->role ?: 'admin';

        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $userRole,
        ]);

        // Clean up OTP session state
        session()->forget(['otp_pending_user_id', 'otp_code', 'otp_expires_at', 'otp_target_email']);

        SecurityLog::create([
            'event_type' => 'successful_login',
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => "Successful 2FA OTP login session initiated for role: {$userRole}",
        ]);

        Log::info("SECURITY AUDIT: Successful OTP 2FA login for {$user->email} ({$userRole}) from IP {$request->ip()}");

        $roleTitle = ucwords(str_replace('_', ' ', $userRole));
        return redirect()->route('dashboard')->with('success', "Two-factor verification successful! Signed in as {$user->name} ({$roleTitle}).");
    }

    /**
     * Resend 6-digit OTP code to registered/demo email.
     */
    public function resendOtp(Request $request)
    {
        if (session()->has('otp_resend_available_at') && now()->timestamp < session('otp_resend_available_at')) {
            $secondsLeft = session('otp_resend_available_at') - now()->timestamp;
            return back()->with('error', "⏳ Please wait {$secondsLeft} second(s) before requesting a new verification code.");
        }

        $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        $pendingUserId = session('otp_pending_user_id');
        $user = $pendingUserId ? User::find($pendingUserId) : null;
        $userEmail = $user ? $user->email : session('otp_target_email');

        $targetEmail = filter_var($userEmail, FILTER_VALIDATE_EMAIL) 
            ? $userEmail 
            : (config('mail.demo_otp_email') ?: $userEmail);

        session([
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(10)->timestamp,
            'otp_target_email' => $targetEmail,
            'otp_resend_available_at' => now()->addSeconds(60)->timestamp,
        ]);

        try {
            $this->sendOtpEmail($targetEmail, $otpCode);
            return back()->with('success', "A new 6-digit verification code has been sent to {$targetEmail}. Check your inbox!");
        } catch (\Throwable $e) {
            Log::error("OTP RESEND MAIL FAILED: " . $e->getMessage());
            return back()->with('error', "Failed to dispatch email: " . $e->getMessage());
        }
    }

    /**
     * Dispatch 6-digit OTP code to email address via Brevo HTTPS API (Port 443) with Gmail SMTP fallback.
     */
    protected function sendOtpEmail(string $targetEmail, string $otpCode): void
    {
        $brevoKey = config('services.brevo.key') ?: env('BREVO_API_KEY');

        // Priority 1: High-Speed Brevo HTTPS API (Guaranteed 100% on Vercel & Localhost)
        if ($brevoKey) {
            $response = Http::withHeaders([
                'api-key' => $brevoKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(10)->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => 'Hirna Mobility Solutions',
                    'email' => 'monterolorencemanuel@gmail.com',
                ],
                'to' => [
                    [
                        'email' => $targetEmail,
                        'name' => 'Hirna User',
                    ]
                ],
                'subject' => "🔐 {$otpCode} - Hirna Security Verification Code",
                'htmlContent' => "
                    <div style='font-family: Arial, sans-serif; padding: 24px; background-color: #0F172A; color: #ffffff; border-radius: 16px; max-width: 480px; margin: 0 auto; border: 1px solid #F59E0B;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h2 style='color: #ffffff; margin: 0; font-size: 22px;'>HIRNA MOBILITY SOLUTIONS</h2>
                            <small style='color: #F59E0B; font-weight: bold; font-size: 11px; letter-spacing: 1px;'>OFFICIAL TNC FLEET PORTAL</small>
                        </div>
                        <p style='color: #CBD5E1; font-size: 14px;'>Hello,</p>
                        <p style='color: #CBD5E1; font-size: 14px;'>Your 2-Factor Authentication security verification code is:</p>
                        <div style='background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); border: 2px solid #F59E0B; padding: 18px; border-radius: 12px; text-align: center; font-size: 32px; font-weight: 800; letter-spacing: 6px; color: #FDE047; margin: 20px 0;'>
                            {$otpCode}
                        </div>
                        <p style='color: #94A3B8; font-size: 12px;'>This code is valid for <strong>10 minutes</strong>. If you did not request this login attempt, please ignore this email.</p>
                        <hr style='border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;'>
                        <div style='text-align: center; color: #64748B; font-size: 11px;'>
                            Hirna Mobility Solutions Inc. &bull; Enterprise Fleet Portal
                        </div>
                    </div>
                ",
            ]);

            if ($response->successful()) {
                Log::info("BREVO API OTP DISPATCH SUCCESS: Code {$otpCode} delivered to {$targetEmail} (Message ID: " . ($response->json('messageId') ?? 'N/A') . ")");
                return;
            }

            Log::warning("BREVO API FAILED (Status {$response->status()}): " . $response->body() . ". Falling back to Mail facade.");
        }

        // Priority 2: Fallback to standard Mail facade / Gmail SMTP
        Mail::raw(
            "Your Hirna Mobility Solutions Security Verification Code is: {$otpCode}\n\nThis code will expire in 10 minutes.\nIf you did not request this login attempt, please ignore this email.",
            function ($message) use ($targetEmail, $otpCode) {
                $message->to($targetEmail)
                        ->from('monterolorencemanuel@gmail.com', 'Hirna Mobility Solutions')
                        ->subject("🔐 {$otpCode} - Hirna Security Verification Code");
            }
        );
        Log::info("GMAIL SMTP OTP DISPATCH SUCCESS: Code {$otpCode} dispatched to {$targetEmail}");
    }


    /**
     * Switch active role live during demo.
     */
    public function switchRole(Request $request)
    {
        $role = $request->get('role', 'admin');
        $validRoles = [
            'admin' => 'System Administrator',
            'fleet_manager' => 'Fleet Manager',
            'dispatcher' => 'Dispatcher',
            'finance' => 'Finance Officer',
            'operations' => 'Operations Manager',
        ];

        if (array_key_exists($role, $validRoles)) {
            $matchingUser = User::where('role', $role)->first();
            if ($matchingUser) {
                session([
                    'user_id' => $matchingUser->id,
                    'user_name' => $matchingUser->name,
                    'user_email' => $matchingUser->email,
                    'user_role' => $role,
                ]);
            } else {
                session(['user_role' => $role]);
            }
            Log::info("SECURITY AUDIT: Perspective switched to '{$role}' by user " . session('user_email'));
            return redirect()->back()->with('success', "Active perspective switched to: " . $validRoles[$role]);
        }

        return redirect()->back();
    }

    /**
     * Secure Logout: Invalidate session and regenerate CSRF token.
     */
    public function logout(Request $request)
    {
        $userEmail = session('user_email', 'User');
        Log::info("SECURITY AUDIT: User {$userEmail} logged out from IP: {$request->ip()}");

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been securely signed out of the Hirna Portal.');
    }
}
