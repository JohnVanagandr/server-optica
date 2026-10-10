<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Exceptions\ApiExceptionHandler;
use App\Http\Middleware\ApiMiddlewareHandler;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php', // Desactivado: tus archivos modulares controlan todo
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // 1. RUTAS DE IDENTIDAD (Públicas)
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/auth.php'));
            
            // 2. RUTAS PÚBLICAS (Catálogos de libre acceso)
            Route::middleware('api')
                ->prefix('api/front')
                ->group(function () {
                    require base_path('routes/public.php');
                });

            // 3. PANEL CLIENTES (Protegido por Sanctum + Onboarding)
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api/client')
                ->group(function () {
                    require base_path('routes/client.php');
                });

            // 4. PANEL ADMINISTRATIVO (Protegido solo por Sanctum)
            // La granularidad de permisos ocurre dentro de routes/admin.php
            Route::middleware(['api', 'auth:sanctum']) 
                ->prefix('api/admin')
                ->group(function () {
                    require base_path('routes/admin.php');
                });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        (new ApiMiddlewareHandler())->bind($middleware);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        (new ApiExceptionHandler())->bind($exceptions);
    })->create();