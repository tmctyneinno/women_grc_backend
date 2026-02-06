<?php
// app/Http/Middleware/Cors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Log the request for debugging
        \Log::info('CORS Middleware Triggered', [
            'origin' => $request->header('Origin'),
            'method' => $request->method(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl()
        ]);
        
        // ALLOWED ORIGINS - MUST INCLUDE www.wgrcfp.org
        $allowedOrigins = [
            // 'https://www.wgrcfp.org',
            // 'https://wgrcfp.org',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
        ];
        
        $origin = $request->header('Origin');
        
        // Handle OPTIONS preflight requests
        if ($request->isMethod('OPTIONS')) {
            \Log::info('CORS OPTIONS Preflight', ['origin' => $origin]);
            
            $response = response('', 200);
        } else {
            $response = $next($request);
        }
        
        // Determine which origin to allow
        $allowOrigin = $origin && in_array($origin, $allowedOrigins) 
            ? $origin 
            : $allowedOrigins[0]; // Default to main domain
        
        // ADD THE REQUIRED CORS HEADERS
        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');
        
        \Log::info('CORS Headers Added', [
            'origin' => $origin,
            'allowed_origin' => $allowOrigin
        ]);
        
        return $response;
    }
}