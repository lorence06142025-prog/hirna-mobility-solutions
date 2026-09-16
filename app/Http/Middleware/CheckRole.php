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

        $userRole = session('user_role', 'admin');

        // Admin has full unrestricted access across all modules
        if ($userRole === 'admin' || empty($roles)) {
            return $next($request);
        }

        // Check if current user role matches permitted roles for this route
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Reject unauthorized roles with strict 403 Forbidden response
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['error' => 'Unauthorized role access.'], 403);
        }

        abort(403, 'Unauthorized access for your assigned account role.');
    }
}

