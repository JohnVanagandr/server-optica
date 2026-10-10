<?php

use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Dashboard\CategoryController;
use App\Http\Controllers\Api\Dashboard\ProductController; 
use Illuminate\Support\Facades\Route;

Route::middleware(['auto_permission'])->group(function () {

    Route::apiResource('products', ProductController::class);
    Route::patch('products/{product}/featured', [ProductController::class, 'toggleFeatured']);
    Route::patch('products/{product}/active', [ProductController::class, 'toggleActive']);   
        
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('users', UserController::class)->only(['index', 'show']);

    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
});