<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado pero su cuenta fue desactivada...
        if (auth()->check() && !auth()->user()->is_active) {
            
            // Destruimos el token por seguridad
            auth()->user()->tokens()->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Tu cuenta ha sido suspendida. Por favor, contacta a soporte.'
            ], 403); // 403 Forbidden
        }

        return $next($request);
    }
}