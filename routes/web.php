<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

require 'admin.php';
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/images/proxy/{path}', function ($path) {
    $storagePath = storage_path('app/public/' . $path);
    
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