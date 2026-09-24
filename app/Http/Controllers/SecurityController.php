<?php

namespace App\Http\Controllers;

use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SecurityController extends Controller
{
    /**
     * Display the Superadmin Security & User Access Control Center.
     */
    public function index()
    {
        $securityLogs = SecurityLog::latest()->paginate(15);
        
        $dbUsers = User::all();
        
        // Hirna Mobility official system role accounts
        $defaultUsers = collect([
            (object)['id' => 1, 'name' => 'Hirna System Admin', 'email' => 'hirna admin', 'job_title' => 'Chief Technology Officer & Admin', 'role' => 'admin', 'status' => 'active', 'avatar_url' => 'https://ui-avatars.com/api/?name=Hirna+System+Admin&background=CE2029&color=fff&size=128'],
            (object)['id' => 2, 'name' => 'Alex Fleet Manager', 'email' => 'hirna fleet', 'job_title' => 'Head of Fleet Operations', 'role' => 'fleet_manager', 'status' => 'active', 'avatar_url' => 'https://ui-avatars.com/api/?name=Alex+Fleet+Manager&background=F59E0B&color=fff&size=128'],
            (object)['id' => 3, 'name' => 'Sarah Dispatcher', 'email' => 'hirna dispatcher', 'job_title' => 'Lead Telematics Dispatcher', 'role' => 'dispatcher', 'status' => 'active', 'avatar_url' => 'https://ui-avatars.com/api/?name=Sarah+Dispatcher&background=10B981&color=fff&size=128'],
            (object)['id' => 4, 'name' => 'Marcus Finance Officer', 'email' => 'hirna finance', 'job_title' => 'Senior Financial Controller', 'role' => 'finance', 'status' => 'active', 'avatar_url' => 'https://ui-avatars.com/api/?name=Marcus+Finance+Officer&background=3B82F6&color=fff&size=128'],
            (object)['id' => 5, 'name' => 'Elena Operations Manager', 'email' => 'hirna operations', 'job_title' => 'Depot & Charging Operations Director', 'role' => 'operations', 'status' => 'active', 'avatar_url' => 'https://ui-avatars.com/api/?name=Elena+Operations+Manager&background=8B5CF6&color=fff&size=128'],
        ]);

        $allUsers = $dbUsers->concat($defaultUsers)->unique('email');

        // Dynamically evaluate lockout status for each user account
        $users = $allUsers->map(function ($usr) {
            $email = Str::lower($usr->email);
            $cleanInput = str_replace([' ', '_', '-', '@', '.'], '', $email);

            $keyUser = Str::transliterate("login_lockout:user_{$usr->id}|" . request()->ip());
            $keyUserLocal = Str::transliterate("login_lockout:user_{$usr->id}|127.0.0.1");
            $keyInput = Str::transliterate("login_lockout:input_{$cleanInput}|" . request()->ip());
            $key1 = Str::transliterate($email . '|' . request()->ip());

            $attemptsUser = RateLimiter::attempts($keyUser);
            $attemptsUserLocal = RateLimiter::attempts($keyUserLocal);
            $attemptsInput = RateLimiter::attempts($keyInput);
            $attempts1 = RateLimiter::attempts($key1);

            $maxAttempts = max($attemptsUser, $attemptsUserLocal, $attemptsInput, $attempts1);
            $isLocked = RateLimiter::tooManyAttempts($keyUser, 3) 
                || RateLimiter::tooManyAttempts($keyUserLocal, 3) 
                || RateLimiter::tooManyAttempts($keyInput, 3) 
                || RateLimiter::tooManyAttempts($key1, 3);

            // Also check latest audit log for un-cleared lockout
            $lastLog = SecurityLog::where('email', $email)->latest()->first();
            if ($lastLog && $lastLog->event_type === 'account_lockout') {
                $isLocked = true;
                $maxAttempts = 3;
            } elseif ($lastLog && in_array($lastLog->event_type, ['admin_unlock', 'successful_login'])) {
                $isLocked = false;
                $maxAttempts = 0;
            }

            $usr->is_locked = $isLocked;
            $usr->attempts_count = $maxAttempts;
            return $usr;
        });

        $lockedUsersCount = $users->where('is_locked', true)->count();
        $totalFailedAttempts = SecurityLog::where('event_type', 'failed_login')->count();
        $totalLockouts = SecurityLog::where('event_type', 'account_lockout')->count();
        $totalHoneypotBlocks = SecurityLog::where('event_type', 'bot_honeypot_blocked')->count();
        $recentLockouts = SecurityLog::where('event_type', 'account_lockout')->latest()->take(10)->get();

        return view('admin.security', compact(
            'securityLogs',
            'users',
            'lockedUsersCount',
            'totalFailedAttempts',
            'totalLockouts',
            'totalHoneypotBlocks',
            'recentLockouts'
        ));
    }

    /**
     * Create new user account from Superadmin Security Console.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'job_title' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:30',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&~^()_+\-=\[\]{};\':"\\\\|,.<>\/?]/',
            ],
            'role' => 'required|string|in:admin,fleet_manager,dispatcher,finance,operations,driver',
        ], [
            'email.unique' => 'This email address is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password fails complexity rules: Must include 1 Uppercase (A-Z), 1 Lowercase (a-z), 1 Number (0-9), and 1 Special Character.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => trim(Str::lower($validated['email'])),
            'job_title' => $validated['job_title'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        SecurityLog::create([
            'event_type' => 'admin_create_user',
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => "Superadmin (" . session('user_email', 'admin@hirna.ph') . ") created new account: {$user->name} ({$user->role})",
        ]);

        Log::info("SECURITY AUDIT: Superadmin created user {$user->email} ({$user->role})");

        return redirect()->back()->with('success', "👤 User Account '{$user->name}' ({$user->email}) created successfully with role '" . ucfirst($user->role) . "'.");
    }

    /**
     * One-Click Superadmin Account & IP Unlock feature.
     * Instantly resets RateLimiter brute-force counters for any specified email or IP address.
     */
    public function unlockUser(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'ip_address' => 'nullable|string',
        ]);

        $email = trim(Str::lower($request->email));
        $cleanInput = str_replace([' ', '_', '-', '@', '.'], '', $email);
        $clientIp = trim($request->ip_address ?? $request->ip());

        $usr = User::where('email', $email)->orWhereRaw("LOWER(REPLACE(email, ' ', '')) = ?", [$cleanInput])->first();

        // Clear all rate limiter key variations for guaranteed lockout removal
        $keysToClear = [
            Str::transliterate("login_lockout:input_{$cleanInput}|" . $clientIp),
            Str::transliterate("login_lockout:input_{$cleanInput}|127.0.0.1"),
            Str::transliterate("login_lockout:input_{$cleanInput}|" . $request->ip()),
            Str::transliterate($email . '|' . $clientIp),
            Str::transliterate($email . '|127.0.0.1'),
            Str::transliterate($email . '|' . $request->ip()),
            Str::transliterate($email),
            $clientIp,
            '127.0.0.1',
            $request->ip(),
        ];

        if ($usr) {
            $keysToClear[] = Str::transliterate("login_lockout:user_{$usr->id}|" . $clientIp);
            $keysToClear[] = Str::transliterate("login_lockout:user_{$usr->id}|127.0.0.1");
            $keysToClear[] = Str::transliterate("login_lockout:user_{$usr->id}|" . $request->ip());
        }

        foreach ($keysToClear as $k) {
            if (!empty($k)) {
                RateLimiter::clear($k);
            }
        }

        // Record Audit Event
        SecurityLog::create([
            'event_type' => 'admin_unlock',
            'email' => $email,
            'ip_address' => $clientIp,
            'user_agent' => $request->userAgent(),
            'details' => "Superadmin (" . session('user_email', 'admin@hirna.ph') . ") manually unlocked account and cleared brute-force rate limiter.",
        ]);

        Log::info("SECURITY AUDIT: Superadmin unlocked account {$email} (IP: {$clientIp})");

        return redirect()->back()->with('success', "🔓 Account Unlocked! Rate limiter lockout completely cleared for {$email}.");
    }

    /**
     * Clear old security logs.
     */
    public function clearLogs()
    {
        SecurityLog::truncate();
        
        SecurityLog::create([
            'event_type' => 'admin_unlock',
            'email' => session('user_email', 'admin@hirna.ph'),
            'ip_address' => request()->ip(),
            'details' => 'Security audit log database truncated by Superadmin.',
        ]);

        return redirect()->back()->with('success', 'Security audit logs cleared successfully.');
    }
}
