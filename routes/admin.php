<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventSpeakerController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\ForumController;
use App\Http\Controllers\Admin\MembershipApprovalController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\MembershipTierController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\PodcastController;
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

        Route::prefix('memberships')->name('memberships.')->group(function () {
            Route::get('/pending', [MembershipApprovalController::class, 'index'])->name('pending');
            Route::patch('/{userMembership}/approve', [MembershipApprovalController::class, 'approve'])->name('approve');
        });

        Route::prefix('membership-plans')->name('membership-plans.')->group(function () {
            Route::get('/', [MembershipController::class, 'index'])->name('index');
            Route::get('/create', [MembershipController::class, 'create'])->name('create');
            Route::post('/', [MembershipController::class, 'store'])->name('store');
            Route::get('/{membership}/edit', [MembershipController::class, 'edit'])->name('edit');
            Route::put('/{membership}', [MembershipController::class, 'update'])->name('update');
            Route::delete('/{membership}', [MembershipController::class, 'destroy'])->name('destroy');

            Route::prefix('{membership}/tiers')->name('tiers.')->group(function () {
                Route::get('/', [MembershipTierController::class, 'index'])->name('index');
                Route::get('/create', [MembershipTierController::class, 'create'])->name('create');
                Route::post('/', [MembershipTierController::class, 'store'])->name('store');
                Route::get('/{tier}/edit', [MembershipTierController::class, 'edit'])->name('edit');
                Route::put('/{tier}', [MembershipTierController::class, 'update'])->name('update');
                Route::delete('/{tier}', [MembershipTierController::class, 'destroy'])->name('destroy');
            });
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

        Route::resource('courses', CourseController::class);
        Route::get('modules', [ModuleController::class, 'all'])->name('modules.all');
        Route::get('lessons', [LessonController::class, 'all'])->name('lessons.all');
        Route::get('quizzes', [QuizController::class, 'all'])->name('quizzes.all');
        Route::get('forums', [ForumController::class, 'index'])->name('forums.index');
        Route::get('forums/{forum}', [ForumController::class, 'show'])->name('forums.show');
        Route::patch('forums/{forum}/deactivate', [ForumController::class, 'deactivate'])->name('forums.deactivate');
        Route::patch('forums/{forum}/activate', [ForumController::class, 'activate'])->name('forums.activate');
        Route::delete('forums/{forum}', [ForumController::class, 'destroy'])->name('forums.destroy');
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::resource('podcasts', PodcastController::class)->except(['show']);
        Route::get('account/change-password', [AccountController::class, 'showChangePassword'])->name('account.password');
        Route::patch('account/change-password', [AccountController::class, 'updatePassword'])->name('account.password.update');

        Route::prefix('courses/{course}/modules')->name('courses.modules.')->group(function () {
            Route::get('/', [ModuleController::class, 'index'])->name('index');
            Route::get('/create', [ModuleController::class, 'create'])->name('create');
            Route::post('/', [ModuleController::class, 'store'])->name('store');
            Route::get('/{module}/edit', [ModuleController::class, 'edit'])->name('edit');
            Route::put('/{module}', [ModuleController::class, 'update'])->name('update');
            Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('courses/{course}/modules/{module}/lessons')->name('courses.modules.lessons.')->group(function () {
            Route::get('/', [LessonController::class, 'index'])->name('index');
            Route::get('/create', [LessonController::class, 'create'])->name('create');
            Route::post('/', [LessonController::class, 'store'])->name('store');
            Route::get('/{lesson}/edit', [LessonController::class, 'edit'])->name('edit');
            Route::put('/{lesson}', [LessonController::class, 'update'])->name('update');
            Route::delete('/{lesson}', [LessonController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('courses/{course}/modules/{module}/quizzes')->name('courses.modules.quizzes.')->group(function () {
            Route::get('/', [QuizController::class, 'index'])->name('index');
            Route::get('/create', [QuizController::class, 'create'])->name('create');
            Route::post('/', [QuizController::class, 'store'])->name('store');
            Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('edit');
            Route::put('/{quiz}', [QuizController::class, 'update'])->name('update');
            Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy');
        });

    });
});
