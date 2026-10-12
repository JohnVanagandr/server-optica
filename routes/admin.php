<?php

use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\LikeAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auto_permission'])->group(function () {
    // Catálogo del SaaS
    Route::apiResource('products', ProductController::class);
    Route::patch('products/{product}/featured', [ProductController::class, 'toggleFeatured']);
    Route::patch('products/{product}/active', [ProductController::class, 'toggleActive']);   
    Route::apiResource('categories', CategoryController::class);
    
    Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);

    Route::apiResource('users', UserController::class)->only(['index', 'show']);
    
    // Analíticas
    Route::get('/analytics/likes', [LikeAnalyticsController::class, 'getStats'])->middleware('permission:likes.index');
});