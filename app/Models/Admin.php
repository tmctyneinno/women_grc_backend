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
        if (in_array($permission, $permissions, true)) {
            return true;
        }

        if (str_contains($permission, '.')) {
            $module = explode('.', $permission)[0] ?? $permission;
            if (in_array($module, $permissions, true)) {
                return true;
            }
        } else {
            foreach ($permissions as $perm) {
                if (str_starts_with($perm, $permission . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canViewTransactions(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        $transactionPerms = ['transactions', 'transactions.view', 'events.view', 'courses.view', 'memberships.view', 'forums.view'];

        foreach ($transactionPerms as $perm) {
            if (in_array($perm, $permissions, true)) {
                return true;
            }
        }

        return false;
    }
}
