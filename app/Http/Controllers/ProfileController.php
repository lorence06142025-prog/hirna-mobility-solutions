<?php

namespace App\Http\Controllers;

use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Get the active authenticated user model instance.
     */
    private function getActiveUser()
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $sessionEmail = session('user_email', 'admin@hirna.ph');
        $user = User::where('email', Str::lower($sessionEmail))->first();

        if (!$user) {
            $user = User::firstOrCreate(
                ['email' => 'admin@hirna.ph'],
                [
                    'name' => session('user_name', 'Hirna System Admin'),
                    'password' => Hash::make('Password@123'),
                    'role' => 'admin',
                    'phone_number' => '+63 917 888 4476',
                    'job_title' => 'System Administrator',
                ]
            );
        }

        return $user;
    }

    /**
     * Render the User Account & Profile Settings view.
     */
    public function index()
    {
        $user = $this->getActiveUser();
        return view('profile.index', compact('user'));
    }

    /**
     * Update basic profile details (Name, Email, Phone Number, Job Title).
     */
    public function updateProfile(Request $request)
    {
        $user = $this->getActiveUser();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:30',
            'job_title' => 'nullable|string|max:100',
        ], [
            'email.unique' => 'This email address is already in use by another account.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'phone_number' => $validated['phone_number'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
        ]);

        // Sync session cache
        session([
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_phone' => $user->phone_number,
            'user_job_title' => $user->job_title,
        ]);

        SecurityLog::create([
            'event_type' => 'profile_update',
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => "User updated personal profile details: {$user->name} ({$user->email})",
        ]);

        return redirect()->back()->with('success', '👤 Profile information updated successfully.');
    }

    /**
     * Securely update account password.
     */
    public function updatePassword(Request $request)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&~^()_+\-=\[\]{};\':"\\\\|,.<>\/?]/',
            ],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_password.min' => 'New password must be at least 8 characters long.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password.regex' => 'New password fails complexity rules: Must include 1 Uppercase (A-Z), 1 Lowercase (a-z), 1 Number (0-9), and 1 Special Character.',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Incorrect current password. Please try again.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        SecurityLog::create([
            'event_type' => 'password_change',
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => "User securely changed account password for {$user->email}",
        ]);

        Log::info("SECURITY AUDIT: Password changed for {$user->email}");

        return redirect()->back()->with('success', '🔒 Password changed successfully. Your account security credentials have been updated.');
    }

    /**
     * Upload and update user profile picture avatar.
     * Supports both standard writeable disk (XAMPP/VPS) and read-only serverless filesystems (Vercel).
     */
    public function updateAvatar(Request $request)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ], [
            'avatar.required' => 'Please select an image file to upload.',
            'avatar.image' => 'The uploaded file must be a valid image (JPEG, PNG, WEBP).',
            'avatar.max' => 'Profile picture size cannot exceed 2 MB.',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $savedLocal = false;
            $avatarPath = null;

            // Attempt 1: Standard local disk storage if writeable
            try {
                $destinationPath = public_path('uploads/avatars');
                if (!file_exists($destinationPath)) {
                    @mkdir($destinationPath, 0777, true);
                }

                if (file_exists($destinationPath) && is_writable($destinationPath)) {
                    if ($user->avatar_path && !str_starts_with($user->avatar_path, 'data:') && file_exists(public_path($user->avatar_path))) {
                        @unlink(public_path($user->avatar_path));
                    }

                    $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $filename);
                    $avatarPath = 'uploads/avatars/' . $filename;
                    $savedLocal = true;
                }
            } catch (\Throwable $e) {
                $savedLocal = false;
            }

            // Attempt 2: Read-Only Serverless Fallback (e.g. Vercel) -> Store optimized Base64 Data URI
            if (!$savedLocal) {
                $avatarPath = $this->convertToBase64Image($file);
            }

            $user->update(['avatar_path' => $avatarPath]);
            session(['user_avatar' => $user->avatar_url]);

            SecurityLog::create([
                'event_type' => 'avatar_update',
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => "User uploaded new profile picture",
            ]);

            return redirect()->back()->with('success', '🖼️ Profile picture uploaded and updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload profile picture. Please try again.');
    }

    /**
     * Convert an uploaded image file into an optimized Base64 Data URI string.
     */
    private function convertToBase64Image($file): string
    {
        $mimeType = $file->getMimeType() ?: 'image/jpeg';
        $filePath = $file->getRealPath();

        // Optional GD Optimization: downscale to 300x300 max to keep payload tiny
        if (function_exists('imagecreatefromstring')) {
            try {
                $rawContent = file_get_contents($filePath);
                $srcImg = @imagecreatefromstring($rawContent);
                if ($srcImg !== false) {
                    $width = imagesx($srcImg);
                    $height = imagesy($srcImg);
                    $maxSize = 300;

                    if ($width > $maxSize || $height > $maxSize) {
                        $ratio = min($maxSize / $width, $maxSize / $height);
                        $newWidth = (int)($width * $ratio);
                        $newHeight = (int)($height * $ratio);

                        $dstImg = imagecreatetruecolor($newWidth, $newHeight);
                        imagealphablending($dstImg, false);
                        imagesavealpha($dstImg, true);

                        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagedestroy($srcImg);
                        $srcImg = $dstImg;
                    }

                    ob_start();
                    if ($mimeType === 'image/png') {
                        imagepng($srcImg, null, 8);
                    } elseif ($mimeType === 'image/webp' && function_exists('imagewebp')) {
                        imagewebp($srcImg, null, 80);
                    } else {
                        imagejpeg($srcImg, null, 85);
                        $mimeType = 'image/jpeg';
                    }
                    $imageData = ob_get_clean();
                    imagedestroy($srcImg);

                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            } catch (\Throwable $e) {
                // Ignore GD error and fall back to raw file content
            }
        }

        $rawContent = file_get_contents($filePath);
        return 'data:' . $mimeType . ';base64,' . base64_encode($rawContent);
    }

    /**
     * Remove custom profile picture and reset to default.
     */
    public function removeAvatar()
    {
        $user = $this->getActiveUser();

        if ($user->avatar_path && !str_starts_with($user->avatar_path, 'data:') && file_exists(public_path($user->avatar_path))) {
            @unlink(public_path($user->avatar_path));
        }

        $user->update(['avatar_path' => null]);
        session()->forget('user_avatar');

        return redirect()->back()->with('success', 'Profile picture reset to default system avatar.');
    }
}
