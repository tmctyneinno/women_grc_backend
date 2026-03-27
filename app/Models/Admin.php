<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\AdminActivityLog;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    public function activityLogs()
    {
        return $this->hasMany(AdminActivityLog::class);
    }

    public function isSuperAdmin(): bool
    {
        $email = strtolower((string) $this->email);
        $superEmails = [
            'enquiries@wgrcfp.org'
        ];

        return $this->role === 'super_admin' || in_array($email, $superEmails, true);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions, true);
    }

    public function canViewTransactions(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        $transactionPerms = ['transactions', 'events', 'courses', 'memberships', 'forums'];

        foreach ($transactionPerms as $perm) {
            if (in_array($perm, $permissions, true)) {
                return true;
            }
        }

        return false;
    }
}
