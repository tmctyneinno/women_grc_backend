<?php
// app/Http/Middleware/Cors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // Log incoming request for debugging
        Log::channel('cors')->info('CORS Request', [
            'origin' => $request->header('Origin'),
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);
        
        // Define allowed origins - BOTH with and without www
        $allowedOrigins = [
            'https://wgrcfp.org',
            'http://wgrcfp.org',
            'https://www.wgrcfp.org',
            'http://www.wgrcfp.org',
            'https://wgrcfp.org:3000',
            'http://wgrcfp.org:3000',
            'https://www.wgrcfp.org:3000',
            'http://www.wgrcfp.org:3000',
            'http://localhost:3000',
            'https://localhost:3000',
            'http://127.0.0.1:3000',
            'https://127.0.0.1:3000',
        ];
        
        $origin = $request->header('Origin');
        
        // If it's an OPTIONS request (preflight), handle it immediately
        if ($request->isMethod('OPTIONS')) {
            Log::channel('cors')->info('OPTIONS Preflight Request', [
                'origin' => $origin,
                'path' => $request->path(),
            ]);
            
            $headers = [
                'Access-Control-Allow-Origin' => $origin && in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0],
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '86400',
            ];
            
            return response()->json([], 200, $headers);
        }
        
        // Process the request
        $response = $next($request);
        
        // Add CORS headers if origin is allowed
        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            
            Log::channel('cors')->info('CORS Headers Added', [
                'origin' => $origin,
                'path' => $request->path(),
            ]);
        } else {
            Log::channel('cors')->warning('Origin not allowed', [
                'origin' => $origin,
                'allowed_origins' => $allowedOrigins,
            ]);
        }
        
        return $response;
    }
}