<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Configuration\Middleware;

class ApiMiddlewareHandler
{
    public static function bind(Middleware $middleware): void
    {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'auto_permission' => \App\Http\Middleware\CheckResourcePermission::class,
        ]);
    }
}