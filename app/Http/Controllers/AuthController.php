<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
     * - Session Fixation Regeneration
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
            // Clear brute-force rate limiter on success
            RateLimiter::clear($throttleKey);

            // Session Fixation Defense: Regenerate session ID
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
                'email' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => "Successful login session initiated for role: {$userRole}",
            ]);

            Log::info("SECURITY AUDIT: Successful login for {$email} ({$userRole}) from IP {$request->ip()}");

            $roleTitle = ucwords(str_replace('_', ' ', $userRole));
            return redirect()->route('dashboard')->with('success', "Signed in as {$user->name} ({$roleTitle}).");
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
