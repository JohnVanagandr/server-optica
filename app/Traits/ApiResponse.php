<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

trait ApiResponse
{
    /**
     * Respuesta Exitosa Estándar
     */
    public function success($data = null, string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    /**
     * Respuesta de Error Estándar
     */
    public function error(string $message = 'Ocurrió un error', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors, // Útil para validaciones
            'data'    => null
        ], $code);
    }

    /**
     * Respuesta Paginada
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $resourceClass, string $message = 'Registros recuperados'): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $resourceClass::collection($paginator),
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ]
        ], 200);
    }

    /**
     * Manejador centralizado de Excepciones (El Investigador)
     */
    public function handleException(\Exception $e, string $contextMessage = 'Error en el sistema'): JsonResponse
    {
        // 1. Registramos el error real de forma silenciosa para el equipo de desarrollo
        Log::error("{$contextMessage} | Detalles: " . $e->getMessage() . " | Archivo: " . $e->getFile() . " | Línea: " . $e->getLine());

        // 2. Determinamos si es un error controlable (ej. 403, 404, 422) o un fallo crítico (500)
        $code = in_array($e->getCode(), [403, 404, 422]) ? $e->getCode() : 500;
        
        // 3. Enviamos el mensaje seguro al cliente usando tu método error()
        $clientMessage = $code === 500 ? 'Ocurrió un error interno en el servidor. Contacte a soporte.' : $e->getMessage();

        return $this->error($clientMessage, $code);
    }    
}