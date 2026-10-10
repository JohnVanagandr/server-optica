<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AutoPermission
{
    /**
     * @param string $module El nombre del módulo  (ej: 'usuarios', 'roles')
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        // 1. Saber qué método del controlador se está intentando ejecutar
        $action = $request->route()->getActionMethod(); // Retorna: index, show, store, update o destroy

        // 2. Nuestro "Diccionario Traductor" de convenciones
        $permissionMap = [
            'index'   => "$module.index",
            'show'    => "$module.show",
            'store'   => "$module.create",
            'update'  => "$module.update",
            'destroy' => "$module.delete",
        ];

        // 3. Buscar qué permiso se necesita para esta acción
        $requiredPermission = $permissionMap[$action] ?? null;

        // 4. Si la acción requiere permiso y el usuario NO lo tiene, lo bloqueamos
        if ($requiredPermission && !$request->user()->can($requiredPermission)) {
            // Lanzamos la excepción de Spatie para que tu bootstrap/app.php devuelva el error 403
            throw UnauthorizedException::forPermissions([$requiredPermission]);
        }

        // 5. Si tiene el permiso, adelante
        return $next($request);
    }
}
