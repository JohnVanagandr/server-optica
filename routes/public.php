<?php

use App\Http\Controllers\Api\Catalog\WidgetController;
use App\Http\Controllers\Api\Front\CategoryController;
use App\Http\Controllers\Api\Front\ProductController;
use Illuminate\Support\Facades\Route;

// Prefijo 'public.' para evitar colisiones con el admin
Route::get('/categories', [CategoryController::class, 'index'])
    ->name('public.categories.index');
    
Route::get('/categories/{category}', [CategoryController::class, 'show'])
    ->name('public.categories.show');
    
Route::get('/products', [ProductController::class, 'index'])
    ->name('public.products.index');
    
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->name('public.products.show');

Route::get('/widgets/{widget}', [WidgetController::class, 'show'])
    ->name('public.widgets.show');

Route::get('/widgets/view/{widget}', [WidgetController::class, 'renderIframe'])
    ->name('public.widget.render');