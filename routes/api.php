<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Api\EventController;

Route::middleware('api')->prefix('v1')->group(function () {
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working!']);
    });
    // Your API routes here
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/featured', [EventController::class, 'featured'])->name('featured');
        Route::get('/upcoming', [EventController::class, 'upcoming'])->name('upcoming');
        Route::get('/{id}', [EventController::class, 'show'])->name('show');
    });
    
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisterController::class, 'register'])->name('register');
        
        Route::post('/login', [LoginController::class, 'login'])->name('login');
        
        // Logout route
        Route::post('/logout', [LogoutController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
        
        // Email verification routes
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        
        Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('verification.send');
    });

});
 

Route::get('/test-cors', function() {
    $headers = [];
    foreach (request()->headers->all() as $key => $value) {
        $headers[$key] = $value[0];
    }
    
    return response()->json([
        'success' => true,
        'origin_header' => request()->header('Origin'),
        'all_headers' => $headers,
        'method' => request()->method(),
        'server_time' => now()->toDateTimeString(),
        'cors_working' => true
    ]);
});

