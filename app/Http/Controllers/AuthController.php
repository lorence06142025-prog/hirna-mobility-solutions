<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Please enter your account email address.',
            'password.required' => 'Please enter your password.',
        ]);

        $email = trim(Str::lower($request->input('email')));
        $throttleKey = Str::transliterate($email . '|' . $request->ip());
        $maxAttempts = 3; // Strict 3 Failed Attempts Lockout Threshold

        // 3. Check Rate Limiter Lockout (Max 3 Attempts for all accounts)
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            SecurityLog::create([
                'event_type' => 'account_lockout',
                'email' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => "Account locked out for {$seconds} seconds due to 3 consecutive failed login attempts",
            ]);
            
            Log::warning("SECURITY LOCKOUT: IP {$request->ip()} locked out on {$email}");
            
            return back()->with('error', "🚨 Security Lockout: Too many failed login attempts (3/3). Your account has been temporarily locked for {$seconds} seconds.");
        }

        // 4. Authenticate Against Database User Records Strictly
        $user = User::where('email', $email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Clear brute-force rate limiter on password verification
            RateLimiter::clear($throttleKey);

            // Generate 6-digit OTP Code
            $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $targetEmail = env('DEMO_OTP_EMAIL') ?: $user->email;

            session([
                'otp_pending_user_id' => $user->id,
                'otp_code' => $otpCode,
                'otp_expires_at' => now()->addMinutes(10)->timestamp,
                'otp_target_email' => $targetEmail,
            ]);

            // Dispatch OTP Email
            try {
                Mail::raw(
                    "Your Hirna Mobility Solutions Security Verification Code is: {$otpCode}\n\nThis code will expire in 10 minutes.\nIf you did not request this, please ignore this email.",
                    function ($message) use ($targetEmail) {
                        $message->to($targetEmail)
                                ->subject("🔐 {$otpCode} - Hirna Security Verification Code");
                    }
                );
                Log::info("OTP DISPATCH SUCCESS: Code {$otpCode} dispatched to {$targetEmail}");
            } catch (\Exception $e) {
                Log::error("OTP DISPATCH FAILED: " . $e->getMessage());
            }

            return redirect()->route('otp.show')->with('success', "A 6-digit verification code has been sent to {$targetEmail}.");
        }

        // 5. Failed Login Attempt: Record Strike in RateLimiter
        RateLimiter::hit($throttleKey, 60); // 60-second decay timer
        $attemptsLeft = RateLimiter::remaining($throttleKey, $maxAttempts);

        SecurityLog::create([
            'event_type' => 'failed_login',
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => "Failed authentication attempt with invalid password. {$attemptsLeft} attempts remaining.",
        ]);

        Log::warning("SECURITY ALERT: Failed login attempt for {$email} from IP {$request->ip()}. {$attemptsLeft} attempts remaining.");

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
        if (!session()->has('otp_pending_user_id')) {
            return redirect()->route('login');
        }

        $userId = session('otp_pending_user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'User session invalid.');
        }

        $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $targetEmail = env('DEMO_OTP_EMAIL') ?: $user->email;

        session([
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(10)->timestamp,
            'otp_target_email' => $targetEmail,
        ]);

        try {
            Mail::raw(
                "Your new Hirna Mobility Solutions Security Verification Code is: {$otpCode}\n\nThis code will expire in 10 minutes.",
                function ($message) use ($targetEmail) {
                    $message->to($targetEmail)
                            ->subject("🔐 {$otpCode} - New Hirna Security Verification Code");
                }
            );
            Log::info("OTP RESENT: Code {$otpCode} sent to {$targetEmail}");
            return back()->with('success', "A new 6-digit verification code has been sent to {$targetEmail}.");
        } catch (\Exception $e) {
            Log::error("OTP RESEND MAIL FAILED: " . $e->getMessage());
            return back()->with('error', "Failed to dispatch email: " . $e->getMessage());
        }
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
            session(['user_role' => $role]);
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
