<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'avatar_path',
        'job_title',
        'last_otp_verified_at',
    ];

    /**
     * Get the user's avatar image URL or fallback default avatar.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar_path)) {
            if (str_starts_with($this->avatar_path, 'data:') || str_starts_with($this->avatar_path, 'http://') || str_starts_with($this->avatar_path, 'https://')) {
                return $this->avatar_path;
            }
            if (file_exists(public_path($this->avatar_path))) {
                return asset($this->avatar_path);
            }
        }

        $name = urlencode($this->name ?? 'User');
        return "https://ui-avatars.com/api/?name={$name}&background=CE2029&color=ffffff&bold=true";
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_otp_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
