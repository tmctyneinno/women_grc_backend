<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventSpeakerController;
use Illuminate\Support\Facades\Route;

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Routes 
   
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
   
 
    // Authenticated Routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Redirect admin root to dashboard
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
    });

    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/create', [EventController::class, 'create'])->name('create');
        
        Route::get('/{id}', [EventController::class, 'show'])->name('show'); // GET single event
        Route::get('/{id}/edit', [EventController::class, 'edit'])->name('edit'); // GET edit form
        
        Route::put('/{id}', [EventController::class, 'update'])->name('update'); // PUT update event
        
        Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [EventController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{id}/gallery', [EventController::class, 'removeGalleryImage'])->name('removeGalleryImage');
        
        // LIST routes (keep at end to avoid conflict)
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/featured', [EventController::class, 'featured'])->name('featured');
        Route::get('/upcoming', [EventController::class, 'upcoming'])->name('upcoming');
        Route::get('/calendar', [EventController::class, 'calendar'])->name('calendar');
        
        // CREATE route (POST method)
        Route::post('/', [EventController::class, 'store'])->name('store');
    });
 
    Route::prefix('events/{event}/speakers')->name('events.speakers.')->group(function () {
        Route::get('/', [EventSpeakerController::class, 'index'])->name('index');
        Route::get('/create', [EventSpeakerController::class, 'create'])->name('create');
        Route::post('/', [EventSpeakerController::class, 'store'])->name('store');
        Route::get('/{speaker}/edit', [EventSpeakerController::class, 'edit'])->name('edit');
        Route::put('/{speaker}', [EventSpeakerController::class, 'update'])->name('update');
        Route::delete('/{speaker}', [EventSpeakerController::class, 'destroy'])->name('destroy');
    });
});