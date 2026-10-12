<?php

use App\Http\Controllers\Api\Front\CategoryController;
use App\Http\Controllers\Api\Front\ProductController;
use App\Http\Controllers\Api\Front\WidgetController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index'])->name('public.categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('public.categories.show');
    
Route::get('/products', [ProductController::class, 'index'])->name('public.products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('public.products.show');

// Vistas públicas del iframe del widget
Route::get('/widgets/{widget}', [WidgetController::class, 'show'])->name('public.widgets.show');
Route::get('/widgets/view/{widget}', [WidgetController::class, 'renderIframe'])->name('public.widget.render');