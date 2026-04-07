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
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\MembershipTierController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\LearningController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\PodcastController;
use App\Http\Controllers\Api\ArticleController;



Route::middleware('api')->prefix('v1')->group(function () {
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working!']);
    });
    // Your API routes here
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/featured', [EventController::class, 'featured'])->name('featured');
        Route::get('/upcoming', [EventController::class, 'upcoming'])->name('upcoming');
        Route::middleware('auth:sanctum')->get('/my-bookings', [EventController::class, 'myBookings']);
        Route::get('/{id}', [EventController::class, 'show'])->name('show');
        Route::post('{event}/book', [EventController::class, 'book']);
    });

    Route::prefix('podcasts')->group(function () {
        Route::get('/', [PodcastController::class, 'index']);
        Route::get('/{podcast}', [PodcastController::class, 'show']);
    });

    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::get('/{article}', [ArticleController::class, 'show']);
    });

    Route::middleware('auth:sanctum')->prefix('articles')->group(function () {
        Route::post('/', [ArticleController::class, 'store']);
    });

    Route::middleware('auth:sanctum')->prefix('podcasts')->group(function () {
        Route::get('/progress/list', [PodcastController::class, 'progress']);
        Route::post('/{podcast}/progress', [PodcastController::class, 'updateProgress']);
    });

    // User profile routes (protected)
    Route::middleware('auth:sanctum')->prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::post('/profile', [UserController::class, 'update'])->name('update');
    });

    Route::middleware('auth:sanctum')->prefix('memberships')->group(function () {
        // Memberships
        Route::get('/', [MembershipController::class, 'index']);
        Route::get('/my-status', [MembershipController::class, 'myStatus']);
        Route::get('/{id}', [MembershipController::class, 'show']);

        // Membership Tiers
        Route::get('/tiers', [MembershipTierController::class, 'index']);
        Route::get('/tiers/{id}', [MembershipTierController::class, 'show']);
    });


    Route::middleware('auth:sanctum')->prefix('carts')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::delete('/{id}', [CartController::class, 'remove']);
        Route::post('/checkout', [PaymentController::class, 'checkout']);
        Route::get('/checkout/status/{sessionId}', [PaymentController::class, 'checkoutStatus']);
    });

    Route::post('/payments/stripe/webhook', [PaymentController::class, 'stripeWebhook']);

    Route::prefix('learning')->group(function () {
        Route::get('/courses', [LearningController::class, 'courses']);
        Route::get('/courses/{course}', [LearningController::class, 'show']);
        Route::get('/certificates/verify/{verificationCode}', [LearningController::class, 'verifyCertificate']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/courses/{course}/purchase/initiate', [LearningController::class, 'initiatePurchase']);
            Route::post('/courses/{course}/purchase/confirm', [LearningController::class, 'confirmPurchase']);
            Route::post('/courses/{course}/enroll', [LearningController::class, 'enroll']);
            Route::get('/my-courses', [LearningController::class, 'myCourses']);
            Route::get('/achievements/{certificate}/download', [LearningController::class, 'downloadCertificate']);
            Route::post('/courses/{course}/modules/{module}/progress', [LearningController::class, 'updateModuleProgress']);
            Route::post('/courses/{course}/modules/{module}/quiz/submit', [LearningController::class, 'submitModuleQuiz']);
            Route::get('/leaderboard', [LearningController::class, 'leaderboard']);
            Route::get('/achievements', [LearningController::class, 'achievements']);
        });
    });

    Route::middleware('auth:sanctum')->prefix('forums')->group(function () {
        Route::get('/', [ForumController::class, 'index']);
        Route::get('/notifications', [ForumController::class, 'notifications']);
        Route::patch('/notifications/{notification}/read', [ForumController::class, 'markNotificationRead']);
        Route::get('/notification-preferences', [ForumController::class, 'notificationPreferences']);
        Route::post('/notification-preferences', [ForumController::class, 'updateNotificationPreferences']);

        Route::get('/{forum}', [ForumController::class, 'show']);
        Route::put('/{forum}', [ForumController::class, 'update']);
        Route::delete('/{forum}', [ForumController::class, 'destroy']);
        Route::post('/{forum}/close', [ForumController::class, 'close']);
        Route::post('/{forum}/archive', [ForumController::class, 'archive']);
        Route::post('/{forum}/reopen', [ForumController::class, 'reopen']);
        Route::post('/{forum}/join', [ForumController::class, 'join']);
        Route::post('/{forum}/leave', [ForumController::class, 'leave']);
        Route::get('/{forum}/members', [ForumController::class, 'members']);
        Route::delete('/{forum}/members/{member}', [ForumController::class, 'removeMember']);
        Route::post('/{forum}/members/{member}/moderator', [ForumController::class, 'assignModerator']);
        Route::get('/{forum}/analytics', [ForumController::class, 'analytics']);

        Route::get('/{forum}/threads', [ForumController::class, 'threads']);
        Route::post('/{forum}/threads', [ForumController::class, 'createThread']);
        Route::put('/{forum}/threads/{thread}', [ForumController::class, 'updateThread']);
        Route::delete('/{forum}/threads/{thread}', [ForumController::class, 'deleteThread']);
        Route::post('/{forum}/threads/{thread}/pin', [ForumController::class, 'pinThread']);

        Route::get('/{forum}/threads/{thread}/posts', [ForumController::class, 'posts']);
        Route::post('/{forum}/threads/{thread}/posts', [ForumController::class, 'createPost']);
        Route::put('/{forum}/threads/{thread}/posts/{post}', [ForumController::class, 'updatePost']);
        Route::delete('/{forum}/threads/{thread}/posts/{post}', [ForumController::class, 'deletePost']);
    });

    Route::middleware('auth:sanctum')->post('/forums/posts/{post}/react', [ForumController::class, 'reactPost']);
    Route::middleware('auth:sanctum')->post('/forums/posts/{post}/report', [ForumController::class, 'reportPost']);

    Route::get('/timezone', [UserController::class, 'timezone'])->name('timezones');
    
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisterController::class, 'register'])->name('register');

        Route::post('resend-email', [EmailVerificationController::class, 'resend'])->middleware('auth:sanctum')->name('verification.resend');
        
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

        // Route::get('/reset', [ForgotPasswordController::class, 'reset'])->name('password.reset');


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
