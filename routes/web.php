<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\MembershipApprovalController;
use App\Http\Controllers\ForumInvitationController;
use Illuminate\Support\Str;


Route::get('/', function () {
    // return view('welcome');
    return redirect('/admin/login');
});

Auth::routes();

require 'admin.php';
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/images/proxy/{path}', function ($path) {
    $path = ltrim($path, '/');

    // Basic traversal guard (route allows `.*`).
    if (str_contains($path, '..')) {
        abort(400);
    }

    // Accept both `featured/...` and `events/featured/...` inputs.
    $storagePath = Str::startsWith($path, 'events/')
        ? storage_path('app/public/' . $path)
        : storage_path('app/public/events/' . $path);
    
    if (!file_exists($storagePath)) {
        abort(404);
    }
    
    $file = file_get_contents($storagePath);
    $mimeType = mime_content_type($storagePath);
    
    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cache-Control', 'public, max-age=31536000');
})->where('path', '.*')->name('image.proxy');

// Add a specific route for event images
Route::get('/proxy/event-images/{path}', function ($path) {
    $path = ltrim($path, '/');

    if (str_contains($path, '..')) {
        abort(400);
    }

    $storagePath = Str::startsWith($path, 'events/')
        ? storage_path('app/public/' . $path)
        : storage_path('app/public/events/' . $path);
    
    if (!file_exists($storagePath)) {
        abort(404);
    }
    
    return response()->file($storagePath, [
        'Content-Type' => mime_content_type($storagePath),
        'Access-Control-Allow-Origin' => '*',
        'Cache-Control' => 'public, max-age=31536000'
    ]);
})->where('path', '.*')->name('proxy.event-images');

Route::get('/reset-password', [ForgotPasswordController::class, 'reset'])->name('reset');

Route::get('/memberships/approve/{userMembership}', [MembershipApprovalController::class, 'approveFromEmail'])
    ->middleware('signed')
    ->name('memberships.email-approve');

Route::get('/forums/invitations/accept/{token}', [ForumInvitationController::class, 'accept'])
    ->name('forums.invitation.accept');
