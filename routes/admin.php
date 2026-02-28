<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventSpeakerController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ModuleController;
use Illuminate\Support\Facades\Route;
 
// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Routes 
    Route::middleware('admin.guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });
 
    // Authenticated Routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Redirect admin root to dashboard
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        }); 

        // Users Management Routes
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/pending', [UserController::class, 'pending'])->name('pend');
            Route::get('/blocked', [UserController::class, 'blocked'])->name('blocked');

            Route::patch('/{user}/approve', [UserController::class, 'approve'])->name('approve');
            Route::patch('/{user}/block', [UserController::class, 'block'])->name('block');
        });

        // Events Management Routes
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

            Route::get('/{event}/bookings', [EventController::class, 'bookings'])->name('bookings');

        });
     
        // Event Speakers Management Routes
        Route::prefix('events/{event}/speakers')->name('events.speakers.')->group(function () {
            Route::get('/', [EventSpeakerController::class, 'index'])->name('index');
            Route::get('/create', [EventSpeakerController::class, 'create'])->name('create');
            Route::post('/', [EventSpeakerController::class, 'store'])->name('store');
            Route::get('/{speaker}/edit', [EventSpeakerController::class, 'edit'])->name('edit');
            Route::put('/{speaker}', [EventSpeakerController::class, 'update'])->name('update');
            Route::delete('/{speaker}', [EventSpeakerController::class, 'destroy'])->name('destroy');
        });

        // Route::prefix('courses')->name('courses.')->group(function () {
        //     Route::resource('/', CourseController::class);
        // });

        Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);

        Route::prefix('courses/{course}/modules')->name('courses.modules.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\ModuleController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\ModuleController::class, 'store'])->name('store');
            Route::get('/{module}/edit', [\App\Http\Controllers\Admin\ModuleController::class, 'edit'])->name('edit');
            Route::put('/{module}', [\App\Http\Controllers\Admin\ModuleController::class, 'update'])->name('update');
            Route::delete('/{module}', [\App\Http\Controllers\Admin\ModuleController::class, 'destroy'])->name('destroy');
        });


        Route::prefix('courses/{course}/modules')->name('courses.modules.')->group(function () {
            Route::get('/', [ModuleController::class, 'index'])->name('index');
            Route::get('/create', [ModuleController::class, 'create'])->name('create');
            Route::post('/', [ModuleController::class, 'store'])->name('store');
            Route::get('/{module}/edit', [ModuleController::class, 'edit'])->name('edit');
            Route::put('/{module}', [ModuleController::class, 'update'])->name('update');
            Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('destroy');
        });

    });
});