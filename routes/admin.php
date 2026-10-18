<?php

use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\PaymentController;
use App\Http\Controllers\Api\Admin\LikeAnalyticsController;
use App\Http\Controllers\Api\Admin\AdvertisementController;
use App\Http\Controllers\Api\Admin\DashboardController; 
use Illuminate\Support\Facades\Route;

Route::middleware(['auto_permission'])->group(function () {
    
    Route::apiResource('products', ProductController::class);
    Route::patch('products/{product}/featured', [ProductController::class, 'toggleFeatured']);
    Route::patch('products/{product}/active', [ProductController::class, 'toggleActive']);   
    Route::apiResource('categories', CategoryController::class);
    
    Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);

    Route::apiResource('users', UserController::class)->only(['index', 'show']);

    Route::apiResource('payments', PaymentController::class)->only(['index']);
    
    Route::get('/analytics/likes', [LikeAnalyticsController::class, 'getStats'])->middleware('permission:likes.index');

    Route::get('/metrics', [DashboardController::class, 'getMetrics'])->middleware('permission:dashboard.admin');

    Route::apiResource('advertisements', AdvertisementController::class);
    Route::patch('advertisements/{advertisement}/toggle-status', [AdvertisementController::class, 'toggleStatus']);
});