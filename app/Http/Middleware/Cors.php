<?php
// app/Http/Middleware/Cors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // List of allowed origins
        $allowedOrigins = [
            'https://wgrcfp.org',
            'http://wgrcfp.org',
            'https://www.wgrcfp.org',
            'http://www.wgrcfp.org',
            'http://localhost:3000',
            'https://localhost:3000',
            'http://127.0.0.1:3000',
            'https://127.0.0.1:3000',
            'http://localhost:8000',
            'https://localhost:8000',
        ];
        
        $origin = $request->header('Origin');
        
        // If there's no Origin header (direct API call), allow it
        if (!$origin) {
            return $next($request);
        }
        
        // Check if the origin is allowed
        if (in_array($origin, $allowedOrigins)) {
            $response = $next($request);
            
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400');
            
            return $response;
        }
        
        // If origin is not allowed, return error
        return response()->json([
            'error' => 'Origin not allowed',
            'origin' => $origin,
            'allowed_origins' => $allowedOrigins
        ], 403);
    }
}