<?php

namespace App\Exceptions;

use App\Traits\ApiResponse; 
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class ApiExceptionHandler
{
    use ApiResponse; 

    /**
     * Quitamos el "static" para poder usar $this->error()
     */
    public function bind(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        $exceptions->render(function (Throwable $e, Request $request) {
            
            if (!$request->is('api/*')) {
                return null;
            }

            // --- 401: No autenticado ---
            if ($e instanceof AuthenticationException) {
                return $this->error('Acceso denegado: No tienes una sesión activa o el token es inválido.', 401);
            }

            // --- 422: Errores de Validación ---
            if ($e instanceof ValidationException) {
                return $this->error('Los datos proporcionados no son válidos.', 422, $e->errors());
            }

            // --- 404: No encontrado ---
            if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                return $this->error('El recurso solicitado no existe.', 404);
            }

            // --- 405: Método no permitido ---
            if ($e instanceof MethodNotAllowedHttpException) {
                return $this->error('Este método HTTP no está permitido para esta ruta.', 405);
            }

            // --- 403: Sin permisos/roles ---
            if ($e instanceof UnauthorizedException) {
                return $this->error('Acceso denegado: No tienes los permisos necesarios para realizar esta acción.', 403, $e->getRequiredPermissions());
            }

            // --- 500: Error de Base de Datos ---
            if ($e instanceof QueryException) {
                return $this->error(
                    config('app.debug') ? $e->getMessage() : 'Ocurrió un problema de comunicación con la base de datos.', 
                    500
                );
            }

            // --- 500: Captura genérica ---
            return $this->error(
                config('app.debug') ? $e->getMessage() : 'Ocurrió un error interno en el servidor. Inténtelo más tarde.', 
                500
            );
        });
    }
}