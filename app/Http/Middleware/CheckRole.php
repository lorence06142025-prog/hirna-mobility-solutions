<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request for Role-Based Access Control (RBAC) & 50-Min OTP Enforcement.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Enforce Login Page First: Redirect to login if no active user session exists
        if (!session()->has('user_id')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthenticated session. Please sign in.'], 401);
            }
            return redirect()->route('login');
        }

        // Enforce Idle Session Timeout (2 Hours Inactivity Threshold to prevent fast 419 Page Expired)
        $idleTimeout = config('session.idle_timeout', 7200);
        $lastActivity = session('last_activity_time');

        if ($lastActivity && (time() - $lastActivity > $idleTimeout)) {
            session()->invalidate();
            session()->regenerateToken();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Session expired due to inactivity.'], 401);
            }

            return redirect()->route('login')->with('error', '⏳ Session Timeout: You have been signed out due to 2 hours of inactivity for security.');
        }

        // Update last activity timestamp on active request
        session(['last_activity_time' => time()]);

        // Enforce Strict 50-Minute Re-OTP Security Threshold (Option A)
        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            session()->invalidate();
            return redirect()->route('login')->with('error', 'User account not found.');
        }

        $isOtpVerifiedWithin50Min = $user->last_otp_verified_at 
            && $user->last_otp_verified_at->gt(now()->subMinutes(50));

        if (!$isOtpVerifiedWithin50Min) {
            // Clear active authenticated session state
            session()->forget(['user_id', 'user_name', 'user_email', 'user_role']);
            
            // Set pending OTP user ID for verification
            $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $targetEmail = (config('mail.demo_otp_email') ?: $user->email);

            session([
                'otp_pending_user_id' => $user->id,
                'otp_code' => $otpCode,
                'otp_expires_at' => now()->addMinutes(10)->timestamp,
                'otp_target_email' => $targetEmail,
                'otp_resend_available_at' => now()->addSeconds(60)->timestamp,
            ]);

            // Dispatch fresh OTP email
            try {
                $authCtrl = new \App\Http\Controllers\AuthController();
                $reflection = new \ReflectionMethod($authCtrl, 'sendOtpEmail');
                $reflection->setAccessible(true);
                $reflection->invoke($authCtrl, $targetEmail, $otpCode);
            } catch (\Throwable $e) {
                Log::error("STRICT 50-MIN RE-OTP DISPATCH ERROR: " . $e->getMessage());
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => '🔐 Security Policy: Your 50-minute OTP verification window has expired. Please re-verify OTP.',
                    'redirect' => route('otp.show')
                ], 401);
            }

            return redirect()->route('otp.show')->with('warning', '🔐 Security Policy: Your 50-minute OTP verification window has expired. A fresh 6-digit verification code has been sent to your email.');
        }

        $userRole = session('user_role', 'admin');

        // Admin has full unrestricted access across all modules
        if ($userRole === 'admin' || empty($roles)) {
            return $next($request);
        }

        // Check if current user role matches permitted roles for this route
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        $roleTitle = ucwords(str_replace('_', ' ', $userRole));
        $deniedMessage = "🚨 Access Denied: Your assigned account role ({$roleTitle}) is not authorized to access that module.";

        // Reject unauthorized roles with strict 403 Forbidden response for API/AJAX
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['error' => $deniedMessage], 403);
        }

        // For web requests, redirect to authorized dashboard with explicit alert
        if ($request->routeIs('dashboard')) {
            abort(403, $deniedMessage);
        }

        return redirect()->route('dashboard')->with('error', $deniedMessage);
    }
}
