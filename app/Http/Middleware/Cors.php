<?php
// app/Http/Middleware/Cors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // Log the request for debugging
        \Log::info('CORS Request', [
            'origin' => $request->header('Origin'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'headers' => $request->headers->all()
        ]);
        
        // List of ALLOWED origins (both with and without www)
        $allowedOrigins = [
            'https://www.wgrcfp.org',
            'http://www.wgrcfp.org',
            'https://wgrcfp.org',
            'http://wgrcfp.org',
            'https://localhost:3000',
            'http://localhost:3000',
            'https://127.0.0.1:3000',
            'http://127.0.0.1:3000',
            'https://localhost:8000',
            'http://localhost:8000',
            'https://127.0.0.1:8000',
            'http://127.0.0.1:8000',
        ];
        
        $origin = $request->header('Origin');
        
        // For OPTIONS preflight requests
        if ($request->isMethod('OPTIONS')) {
            \Log::info('CORS OPTIONS Preflight', [
                'origin' => $origin,
                'path' => $request->path()
            ]);
            
            $headers = [
                'Access-Control-Allow-Origin' => $origin ?: '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '86400', // 24 hours
            ];
            
            return response('', 200, $headers);
        }
        
        // Process the request
        $response = $next($request);
        
        // Determine which origin to allow
        if ($origin && in_array($origin, $allowedOrigins)) {
            $allowOrigin = $origin;
        } else {
            // For development/testing, you might want to allow all
            // $allowOrigin = '*';
            $allowOrigin = 'https://www.wgrcfp.org'; // Default to main domain
        }
        
        // Add CORS headers
        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');
        
        \Log::info('CORS Headers Added', [
            'origin' => $origin,
            'allowed_origin' => $allowOrigin,
            'path' => $request->path()
        ]);
        
        return $response;
    }
}