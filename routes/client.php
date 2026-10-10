<?php

use App\Http\Controllers\Api\Catalog\ProductLikeController;
use App\Http\Controllers\Api\Client\SubscriptionController;
use App\Http\Controllers\Api\Client\WidgetController;
use Illuminate\Support\Facades\Route;

// EL CANDADO ABSOLUTO
Route::middleware(['auto_permission'])->group(function () {    
        
        // ==========================================
        // MÓDULO DE WIDGETS Y CATÁLOGOS
        // ==========================================
        Route::post('/widget/publish', [WidgetController::class, 'publish']);
        Route::get('/widget/catalog', [WidgetController::class, 'catalog']);

        // ==========================================
        // MÓDULO DE INTERACCIÓN (PRODUCTOS)
        // ==========================================
        Route::post('/products/{product}/like', [ProductLikeController::class, 'toggle'])
            ->name('products.like');
            
        // ==========================================
        // MÓDULO DE PLANES Y SUSCRIPCIONES
        // ==========================================
        // Permite al cliente ver el catálogo de planes disponibles
        Route::get('/plans', [SubscriptionController::class, 'index'])
            ->name('plans.index');

        // Procesa la simulación (y futuro pago real) de la suscripción
        Route::post('/suscripcion', [SubscriptionController::class, 'subscribe'])
            ->name('plans.subscribe');
 

});