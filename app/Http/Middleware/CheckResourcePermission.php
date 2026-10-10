<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckResourcePermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Obtenemos el nombre interno de la ruta (Ej: "products.index")
        $routeName = $request->route()->getName();

        // Si la ruta no tiene nombre, la dejamos pasar (útil para rutas ignoradas)
        if (! $routeName) {
            return $next($request);
        }

        // 2. Validamos si el usuario actual tiene el permiso exacto
        // El método can() automáticamente consulta a Spatie y a nuestro AppServiceProvider
        if ($request->user() && ! $request->user()->can($routeName)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Acceso denegado: No posees el permiso requerido [{$routeName}].",
                'data'    => null
            ], 403);
        }

        return $next($request);
    }
}