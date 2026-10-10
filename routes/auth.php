<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Account\AccountController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication & Identity Routes
|--------------------------------------------------------------------------
*/

// PUBLIC ROUTES (No token required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    
    // Rutas de reseteo de contraseña
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.reset');
});

// PROTECTED ROUTES (Sanctum Token required)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
});