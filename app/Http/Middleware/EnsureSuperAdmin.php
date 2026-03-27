<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $admin = auth('admin')->user();

        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
