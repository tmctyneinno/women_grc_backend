<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectIfAdminAuthenticated
{
    public function handle(Request $request, Closure $next, $guard = 'admin')
    {
        Log::info('RedirectIfAdminAuthenticated middleware triggered', [
            'path' => $request->path(),
            'url' => $request->url(),
            'is_admin_authenticated' => Auth::guard($guard)->check(),
            'guard' => $guard,
        ]);
        
        if (Auth::guard($guard)->check()) {
            Log::info('Admin is authenticated, redirecting to dashboard');
            return redirect()->route('admin.dashboard');
        }

        Log::info('Admin is not authenticated, proceeding');
        return $next($request);
    }
}