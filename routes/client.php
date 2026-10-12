<?php

use App\Http\Controllers\Api\Client\SubscriptionController;
use App\Http\Controllers\Api\Client\WidgetController;
use App\Http\Controllers\Api\Client\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auto_permission'])->group(function () {    
        
    // ==========================================
    // MÓDULO DE CATÁLOGO / SIMULADOR (product_user)
    // ==========================================
    Route::get('/widget/catalog', [WidgetController::class, 'catalog']);
    Route::post('/widget/toggle', [WidgetController::class, 'toggleCatalogProduct']);
    Route::post('/widget/publish', [WidgetController::class, 'publish']);
    
    // 🚀 EL PUENTE: La ruta híbrida unificada
    Route::post('/sync-state', [WidgetController::class, 'syncState']);

    // ==========================================
    // MÓDULO DE DESEOS (likes)
    // ==========================================
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::post('/favorites/convert', [FavoriteController::class, 'convertFavoritesToCart']);
        
    // ==========================================
    // MÓDULO DE PLANES Y SUSCRIPCIONES
    // ==========================================
    Route::get('/plans', [SubscriptionController::class, 'index'])->name('plans.index');
    Route::post('/suscripcion', [SubscriptionController::class, 'subscribe'])->name('plans.subscribe');
});