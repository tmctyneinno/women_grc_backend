<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            abort(403);
        }

        if ($permission === 'transactions') {
            if (!$admin->canViewTransactions()) {
                abort(403);
            }

            return $next($request);
        }

        if (!$admin->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
