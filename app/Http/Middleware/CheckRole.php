<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request for Role-Based Access Control (RBAC).
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

        // Enforce Idle Session Timeout (5 Minutes Inactivity Threshold)
        $idleTimeout = config('session.idle_timeout', 300);
        $lastActivity = session('last_activity_time');

        if ($lastActivity && (time() - $lastActivity > $idleTimeout)) {
            session()->invalidate();
            session()->regenerateToken();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Session expired due to inactivity.'], 401);
            }

            return redirect()->route('login')->with('error', '⏳ Session Timeout: You have been signed out due to 5 minutes of inactivity for security.');
        }

        // Update last activity timestamp on active request
        session(['last_activity_time' => time()]);

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

