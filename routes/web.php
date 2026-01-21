<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/vue', function () {
    return view('vue');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/api/user', [AuthController::class, 'user']);
    Route::post('/api/user/avatar', [\App\Http\Controllers\UserController::class, 'uploadAvatar']);
    Route::put('/api/user', [\App\Http\Controllers\UserController::class, 'update']);
    Route::get('/api/users', [\App\Http\Controllers\UserController::class, 'index']);
    Route::post('/api/users', [\App\Http\Controllers\UserController::class, 'store']);
    // Catch-all meant for Vue Router
    Route::get('/{any}', function () {
        return response()
            ->view('dashboard')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    })->where('any', '.*');
});