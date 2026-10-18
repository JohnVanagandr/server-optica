<?php

use App\Http\Controllers\Api\Front\CategoryController;
use App\Http\Controllers\Api\Front\ProductController;
use App\Http\Controllers\Api\Webhooks\WompiWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index'])->name('public.categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('public.categories.show');
    
Route::get('/products', [ProductController::class, 'index'])->name('public.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('public.products.show');

// Webhook de Wompi (Público, sin Auth)
Route::post('/webhooks/wompi', [WompiWebhookController::class, 'handle']);