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
use App\Http\Controllers\Admin\ForumBannedWordController;
use App\Http\Controllers\Admin\MembershipApprovalController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\MembershipTierController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\PodcastController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AdminActivityController;
use App\Http\Controllers\Admin\ArticleController;
use Illuminate\Support\Facades\Route;
 
// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Routes 
    Route::middleware('admin.guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });
 
    // Authenticated Routes
    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Redirect admin root to dashboard
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        }); 

        // Users Management Routes
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index')->middleware('admin.permission:users.view');
            Route::get('/pending', [UserController::class, 'pending'])->name('pend')->middleware('admin.permission:users.view');
            Route::get('/blocked', [UserController::class, 'blocked'])->name('blocked')->middleware('admin.permission:users.view');
            Route::get('/{user}/profile', [UserController::class, 'profile'])->name('profile')->middleware('admin.permission:users.view');

            Route::patch('/{user}/approve', [UserController::class, 'approve'])->name('approve')->middleware('admin.permission:users.approve');
            Route::patch('/{user}/block', [UserController::class, 'block'])->name('block')->middleware('admin.permission:users.block');
            Route::patch('/{user}/unblock', [UserController::class, 'unblock'])->name('unblock')->middleware('admin.permission:users.unblock');
        });

        Route::prefix('mentors')->name('mentors.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MentorController::class, 'index'])->name('index')->middleware('admin.permission:mentors.view');
            Route::get('/create', [\App\Http\Controllers\Admin\MentorController::class, 'create'])->name('create')->middleware('admin.permission:mentors.create');
            Route::post('/', [\App\Http\Controllers\Admin\MentorController::class, 'store'])->name('store')->middleware('admin.permission:mentors.create');
            Route::get('/{mentor}/edit', [\App\Http\Controllers\Admin\MentorController::class, 'edit'])->name('edit')->middleware('admin.permission:mentors.update');
            Route::put('/{mentor}', [\App\Http\Controllers\Admin\MentorController::class, 'update'])->name('update')->middleware('admin.permission:mentors.update');
            Route::patch('/{mentor}/toggle', [\App\Http\Controllers\Admin\MentorController::class, 'toggle'])->name('toggle')->middleware('admin.permission:mentors.update');
            Route::get('/applications', [\App\Http\Controllers\Admin\MentorApplicationController::class, 'index'])->name('applications')->middleware('admin.permission:mentors.view');
            Route::post('/applications/{application}/approve', [\App\Http\Controllers\Admin\MentorApplicationController::class, 'approve'])->name('applications.approve')->middleware('admin.permission:mentors.update');
            Route::post('/applications/{application}/decline', [\App\Http\Controllers\Admin\MentorApplicationController::class, 'decline'])->name('applications.decline')->middleware('admin.permission:mentors.update');
        });

        // Admins Management (Super Admin only)
        Route::prefix('admins')->name('admins.')->middleware('admin.super')->group(function () {
            Route::get('/', [AdminManagementController::class, 'index'])->name('index');
            Route::get('/create', [AdminManagementController::class, 'create'])->name('create');
            Route::post('/', [AdminManagementController::class, 'store'])->name('store');
            Route::get('/{admin}/edit', [AdminManagementController::class, 'edit'])->name('edit');
            Route::put('/{admin}', [AdminManagementController::class, 'update'])->name('update');
            Route::delete('/{admin}', [AdminManagementController::class, 'destroy'])->name('destroy');
        });
        Route::get('/admin-activity', [AdminActivityController::class, 'index'])->name('admins.activity')->middleware('admin.super');

        Route::prefix('memberships')->name('memberships.')->group(function () {
            Route::get('/pending', [MembershipApprovalController::class, 'index'])->name('pending')->middleware('admin.permission:memberships.approve');
            Route::patch('/{userMembership}/approve', [MembershipApprovalController::class, 'approve'])->name('approve')->middleware('admin.permission:memberships.approve');
        });

        Route::prefix('membership-plans')->name('membership-plans.')->group(function () {
            Route::get('/', [MembershipController::class, 'index'])->name('index')->middleware('admin.permission:memberships.view');
            Route::get('/create', [MembershipController::class, 'create'])->name('create')->middleware('admin.permission:memberships.create');
            Route::post('/', [MembershipController::class, 'store'])->name('store')->middleware('admin.permission:memberships.create');
            Route::get('/{membership}/edit', [MembershipController::class, 'edit'])->name('edit')->middleware('admin.permission:memberships.update');
            Route::put('/{membership}', [MembershipController::class, 'update'])->name('update')->middleware('admin.permission:memberships.update');
            Route::delete('/{membership}', [MembershipController::class, 'destroy'])->name('destroy')->middleware('admin.permission:memberships.delete');

            Route::prefix('{membership}/tiers')->name('tiers.')->group(function () {
                Route::get('/', [MembershipTierController::class, 'index'])->name('index')->middleware('admin.permission:memberships.view');
                Route::get('/create', [MembershipTierController::class, 'create'])->name('create')->middleware('admin.permission:memberships.create');
                Route::post('/', [MembershipTierController::class, 'store'])->name('store')->middleware('admin.permission:memberships.create');
                Route::get('/{tier}/edit', [MembershipTierController::class, 'edit'])->name('edit')->middleware('admin.permission:memberships.update');
                Route::put('/{tier}', [MembershipTierController::class, 'update'])->name('update')->middleware('admin.permission:memberships.update');
                Route::delete('/{tier}', [MembershipTierController::class, 'destroy'])->name('destroy')->middleware('admin.permission:memberships.delete');
            });
        });

        // Events Management Routes
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('/', [EventController::class, 'index'])->name('index')->middleware('admin.permission:events.view');
            Route::get('/featured', [EventController::class, 'featured'])->name('featured')->middleware('admin.permission:events.view');
            Route::get('/upcoming', [EventController::class, 'upcoming'])->name('upcoming')->middleware('admin.permission:events.view');
            Route::get('/calendar', [EventController::class, 'calendar'])->name('calendar')->middleware('admin.permission:events.view');

            Route::get('/create', [EventController::class, 'create'])->name('create')->middleware('admin.permission:events.create');
            Route::post('/', [EventController::class, 'store'])->name('store')->middleware('admin.permission:events.create');

            Route::get('/{id}', [EventController::class, 'show'])->name('show')->middleware('admin.permission:events.view');
            Route::get('/{id}/edit', [EventController::class, 'edit'])->name('edit')->middleware('admin.permission:events.update');
            Route::put('/{id}', [EventController::class, 'update'])->name('update')->middleware('admin.permission:events.update');
            Route::patch('/{id}/status', [EventController::class, 'updateStatus'])->name('updateStatus')->middleware('admin.permission:events.update');
            Route::delete('/{id}/gallery', [EventController::class, 'removeGalleryImage'])->name('removeGalleryImage')->middleware('admin.permission:events.update');

            Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy')->middleware('admin.permission:events.delete');

            Route::get('/{event}/bookings', [EventController::class, 'bookings'])->name('bookings')->middleware('admin.permission:events.view');
            Route::get('/{event}/waitlist', [EventController::class, 'waitlist'])->name('waitlist')->middleware('admin.permission:events.view');
            Route::delete('/{event}/bookings/{booking}', [EventController::class, 'deleteBooking'])->name('bookings.delete')->middleware('admin.permission:events.delete');
            Route::get('/{event}/bookings/email', [EventController::class, 'emailBookingsForm'])->name('bookings.email.form')->middleware('admin.permission:events.update');
            Route::post('/{event}/bookings/email', [EventController::class, 'emailBookings'])->name('bookings.email')->middleware('admin.permission:events.update');
        });
     
        // Event Speakers Management Routes
        Route::prefix('events/{event}/speakers')->name('events.speakers.')->group(function () {
            Route::get('/', [EventSpeakerController::class, 'index'])->name('index')->middleware('admin.permission:events.view');
            Route::get('/create', [EventSpeakerController::class, 'create'])->name('create')->middleware('admin.permission:events.update');
            Route::post('/', [EventSpeakerController::class, 'store'])->name('store')->middleware('admin.permission:events.update');
            Route::get('/{speaker}/edit', [EventSpeakerController::class, 'edit'])->name('edit')->middleware('admin.permission:events.update');
            Route::put('/{speaker}', [EventSpeakerController::class, 'update'])->name('update')->middleware('admin.permission:events.update');
            Route::delete('/{speaker}', [EventSpeakerController::class, 'destroy'])->name('destroy')->middleware('admin.permission:events.delete');
        });

        // Route::prefix('courses')->name('courses.')->group(function () {
        //     Route::resource('/', CourseController::class);
        // });

        Route::get('courses', [CourseController::class, 'index'])->name('courses.index')->middleware('admin.permission:courses.view');
        Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create')->middleware('admin.permission:courses.create');
        Route::post('courses', [CourseController::class, 'store'])->name('courses.store')->middleware('admin.permission:courses.create');
        Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show')->middleware('admin.permission:courses.view');
        Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit')->middleware('admin.permission:courses.update');
        Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update')->middleware('admin.permission:courses.update');
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy')->middleware('admin.permission:courses.delete');

        Route::get('modules', [ModuleController::class, 'all'])->name('modules.all')->middleware('admin.permission:courses.view');
        Route::get('lessons', [LessonController::class, 'all'])->name('lessons.all')->middleware('admin.permission:courses.view');
        Route::get('quizzes', [QuizController::class, 'all'])->name('quizzes.all')->middleware('admin.permission:courses.view');
        Route::get('forums', [ForumController::class, 'index'])->name('forums.index')->middleware('admin.permission:forums.view');
        Route::get('forums/create', [ForumController::class, 'create'])->name('forums.create')->middleware('admin.permission:forums.create');
        Route::post('forums', [ForumController::class, 'store'])->name('forums.store')->middleware('admin.permission:forums.create');
        Route::get('forums/banned-words', [ForumBannedWordController::class, 'index'])->name('forums.banned-words.index')->middleware('admin.permission:forums.moderate');
        Route::post('forums/banned-words', [ForumBannedWordController::class, 'store'])->name('forums.banned-words.store')->middleware('admin.permission:forums.moderate');
        Route::patch('forums/banned-words/{word}/toggle', [ForumBannedWordController::class, 'toggle'])->name('forums.banned-words.toggle')->middleware('admin.permission:forums.moderate');
        Route::delete('forums/banned-words/{word}', [ForumBannedWordController::class, 'destroy'])->name('forums.banned-words.destroy')->middleware('admin.permission:forums.moderate');
        Route::get('forums/{forum}', [ForumController::class, 'show'])->name('forums.show')->middleware('admin.permission:forums.view');
        Route::patch('forums/{forum}/deactivate', [ForumController::class, 'deactivate'])->name('forums.deactivate')->middleware('admin.permission:forums.update');
        Route::patch('forums/{forum}/activate', [ForumController::class, 'activate'])->name('forums.activate')->middleware('admin.permission:forums.update');
        Route::delete('forums/{forum}', [ForumController::class, 'destroy'])->name('forums.destroy')->middleware('admin.permission:forums.delete');
        Route::patch('forums/{forum}/memberships/{membership}/approve', [ForumController::class, 'approveMembership'])->name('forums.memberships.approve')->middleware('admin.permission:forums.moderate');
        Route::patch('forums/{forum}/memberships/{membership}/reject', [ForumController::class, 'rejectMembership'])->name('forums.memberships.reject')->middleware('admin.permission:forums.moderate');
        Route::post('forums/{forum}/invites', [ForumController::class, 'inviteByEmail'])->name('forums.invites.send')->middleware('admin.permission:forums.invite');
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index')->middleware('admin.permission:transactions.view');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show')->middleware('admin.permission:transactions.view');

        Route::get('podcasts', [PodcastController::class, 'index'])->name('podcasts.index')->middleware('admin.permission:podcasts.view');
        Route::get('podcasts/create', [PodcastController::class, 'create'])->name('podcasts.create')->middleware('admin.permission:podcasts.create');
        Route::post('podcasts', [PodcastController::class, 'store'])->name('podcasts.store')->middleware('admin.permission:podcasts.create');
        Route::get('podcasts/{podcast}/edit', [PodcastController::class, 'edit'])->name('podcasts.edit')->middleware('admin.permission:podcasts.update');
        Route::put('podcasts/{podcast}', [PodcastController::class, 'update'])->name('podcasts.update')->middleware('admin.permission:podcasts.update');
        Route::delete('podcasts/{podcast}', [PodcastController::class, 'destroy'])->name('podcasts.destroy')->middleware('admin.permission:podcasts.delete');

        Route::prefix('articles')->name('articles.')->group(function () {
            Route::get('/', [ArticleController::class, 'index'])->name('index')->middleware('admin.permission:articles.view');
            Route::get('/create', [ArticleController::class, 'create'])->name('create')->middleware('admin.permission:articles.create');
            Route::post('/', [ArticleController::class, 'store'])->name('store')->middleware('admin.permission:articles.create');
            Route::get('/{article}/edit', [ArticleController::class, 'edit'])->name('edit')->middleware('admin.permission:articles.update');
            Route::put('/{article}', [ArticleController::class, 'update'])->name('update')->middleware('admin.permission:articles.update');
            Route::patch('/{article}/approve', [ArticleController::class, 'approve'])->name('approve')->middleware('admin.permission:articles.approve');
            Route::patch('/{article}/reject', [ArticleController::class, 'reject'])->name('reject')->middleware('admin.permission:articles.approve');
            Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy')->middleware('admin.permission:articles.delete');
        });
        Route::get('account/change-password', [AccountController::class, 'showChangePassword'])->name('account.password');
        Route::patch('account/change-password', [AccountController::class, 'updatePassword'])->name('account.password.update');

        Route::prefix('courses/{course}/modules')->name('courses.modules.')->group(function () {
            Route::get('/', [ModuleController::class, 'index'])->name('index')->middleware('admin.permission:courses.view');
            Route::get('/create', [ModuleController::class, 'create'])->name('create')->middleware('admin.permission:courses.create');
            Route::post('/', [ModuleController::class, 'store'])->name('store')->middleware('admin.permission:courses.create');
            Route::get('/{module}/edit', [ModuleController::class, 'edit'])->name('edit')->middleware('admin.permission:courses.update');
            Route::put('/{module}', [ModuleController::class, 'update'])->name('update')->middleware('admin.permission:courses.update');
            Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('destroy')->middleware('admin.permission:courses.delete');
        });

        Route::prefix('courses/{course}/modules/{module}/lessons')->name('courses.modules.lessons.')->group(function () {
            Route::get('/', [LessonController::class, 'index'])->name('index')->middleware('admin.permission:courses.view');
            Route::get('/create', [LessonController::class, 'create'])->name('create')->middleware('admin.permission:courses.create');
            Route::post('/', [LessonController::class, 'store'])->name('store')->middleware('admin.permission:courses.create');
            Route::get('/{lesson}/edit', [LessonController::class, 'edit'])->name('edit')->middleware('admin.permission:courses.update');
            Route::put('/{lesson}', [LessonController::class, 'update'])->name('update')->middleware('admin.permission:courses.update');
            Route::delete('/{lesson}', [LessonController::class, 'destroy'])->name('destroy')->middleware('admin.permission:courses.delete');
        });

        Route::prefix('courses/{course}/modules/{module}/quizzes')->name('courses.modules.quizzes.')->group(function () {
            Route::get('/', [QuizController::class, 'index'])->name('index')->middleware('admin.permission:courses.view');
            Route::get('/create', [QuizController::class, 'create'])->name('create')->middleware('admin.permission:courses.create');
            Route::post('/', [QuizController::class, 'store'])->name('store')->middleware('admin.permission:courses.create');
            Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('edit')->middleware('admin.permission:courses.update');
            Route::put('/{quiz}', [QuizController::class, 'update'])->name('update')->middleware('admin.permission:courses.update');
            Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy')->middleware('admin.permission:courses.delete');
        });

    });
});
