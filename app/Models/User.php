<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'linkedin_profile',
        'google_id',
        'is_google_account',
        'email_verified_at',
        // Add other fields if using comprehensive migration
        'profile_picture',
        'phone_number',
        'job_title',
        'company',
        'last_login_at',
        'last_login_ip',
        'timezone',
        'preferences'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id', // Hide sensitive OAuth IDs
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
            'password' => 'hashed',
            'is_google_account' => 'boolean',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    /**
     * Interact with the linkedin profile URL.
     */
    protected function linkedinProfile(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => $this->formatLinkedInUrl($value),
        );
    }

    /**
     * Format LinkedIn URL to ensure consistency.
     */
    private function formatLinkedInUrl(string $url): string
    {
        $url = trim($url);
        
        if (empty($url)) {
            return $url;
        }
        
        // Remove any existing https:// or http://
        $url = preg_replace('#^https?://#', '', $url);
        
        // Ensure it starts with linkedin.com/in/
        if (!str_contains($url, 'linkedin.com/in/')) {
            if (str_contains($url, 'linkedin.com/')) {
                $url = str_replace('linkedin.com/', 'linkedin.com/in/', $url);
            } else {
                $url = 'linkedin.com/in/' . ltrim($url, '/');
            }
        }
        
        return 'https://' . $url;
    }
}