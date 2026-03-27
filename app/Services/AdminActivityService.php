<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;

class AdminActivityService
{
    public static function log(?Admin $admin, string $action, ?Model $target = null, array $metadata = [], ?string $description = null): void
    {
        if (!$admin) {
            return;
        }

        AdminActivityLog::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'target_type' => $target ? $target->getMorphClass() : null,
            'target_id' => $target?->getKey(),
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 512),
        ]);
    }
}
