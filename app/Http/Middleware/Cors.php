<?php
// app/Http/Middleware/Cors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // Define ALL possible domains and subdomains
        $allowedOrigins = [
            'https://wgrcfp.org',
            'http://wgrcfp.org',
            'https://www.wgrcfp.org',    // ADD THIS
            'http://www.wgrcfp.org',     // ADD THIS
            'https://wgrcfp.org:3000',
            'http://wgrcfp.org:3000',
            'http://localhost:3000',
            'https://localhost:3000',
            'http://127.0.0.1:3000',
            'https://127.0.0.1:3000',
            'http://localhost:8000',
            'https://localhost:8000',
        ];
        
        $origin = $request->header('Origin');
        
        // Log for debugging
        \Log::info('CORS Middleware', [
            'origin' => $origin,
            'method' => $request->method(),
            'path' => $request->path(),
            'allowed_origins' => $allowedOrigins
        ]);
        
        // If there's no Origin header (direct API call), allow it
        if (!$origin) {
            return $next($request);
        }
        
        // Check if the origin is in allowed list
        $isAllowed = in_array($origin, $allowedOrigins);
        
        // For OPTIONS requests (preflight), we need to return headers immediately
        if ($request->isMethod('OPTIONS')) {
            $headers = [
                'Access-Control-Allow-Origin' => $isAllowed ? $origin : $allowedOrigins[0],
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '86400', // 24 hours
            ];
            
            return response()->json([], 200, $headers);
        }
        
        // For regular requests
        if ($isAllowed) {
            $response = $next($request);
            
            // Add CORS headers
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            
            return $response;
        }
        
        // Origin not allowed
        return response()->json([
            'error' => 'CORS: Origin not allowed',
            'origin' => $origin,
            'allowed_origins' => $allowedOrigins
        ], 403);
    }
}