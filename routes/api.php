<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;



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

    // User profile routes (protected)
    Route::middleware('auth:sanctum')->prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'update'])->name('update');
    });
    
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisterController::class, 'register'])->name('register');
        
        Route::post('/login', [LoginController::class, 'login'])->name('login');
        
        Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
        
        // // Email verification routes
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        
        Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('verification.send');

        // Redirect user to OAuth provider
        Route::get('/{provider}/redirect', [OAuthController::class, 'redirect']);

        // Handle OAuth callback
        Route::get('/{provider}/callback', [OAuthController::class, 'callback']);


        //forgot password and reset password
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);

        Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('reset-password');


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

