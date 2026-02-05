<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
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
    Route::get('/test-cors', function() {
        return response()->json([
            'success' => true,
            'message' => 'CORS is working!',
            'origin' => request()->header('Origin'),
            'timestamp' => now()->toDateTimeString()
        ]);
    });
});
 
Route::get('/test-cors', function() {
    return response()->json([
        'success' => true,
        'message' => 'CORS is working!',
        'origin' => request()->header('Origin'),
        'timestamp' => now()->toDateTimeString()
    ]);
});