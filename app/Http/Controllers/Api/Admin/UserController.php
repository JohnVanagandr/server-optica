<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->integer('per_page', 10);

            // 🚀 Usamos el scope de Spatie ->role('client') en lugar de ->where('role', 'client')
            $users = User::role('client')
                ->with(['plan'])
                ->withCount([
                    'likedProducts as wishlist_count',     
                    'draftCartProducts as cart_count',     
                    'catalogProducts as widget_count'      
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->paginatedResponse(
                $users,
                UserResource::class,
                'Listado de clientes obtenido con éxito.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al listar los clientes');
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            // 🚀 Usamos Spatie aquí también
            $user = User::role('client')
                ->with(['plan'])
                ->withCount([
                    'likedProducts as wishlist_count',
                    'draftCartProducts as cart_count',
                    'catalogProducts as widget_count'
                ])
                ->findOrFail($id);

            return $this->success(
                new UserResource($user),
                'Detalle completo del cliente obtenido con éxito.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('El cliente solicitado no existe o no tienes permisos.', 404);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener el cliente');
        }
    }

    public function toggleStatus(string $id): JsonResponse
    {
        try {
            // 🚀 Validamos que sea un cliente usando Spatie
            $user = User::role('client')->findOrFail($id);

            $user->is_active = !$user->is_active;
            $user->save();

            // Si fue suspendido, matamos sus sesiones activas de inmediato
            if (!$user->is_active) {
                $user->tokens()->delete();
            }

            $estadoString = $user->is_active ? 'activado' : 'desactivado (suspendido)';

            return $this->success(
                new UserResource($user),
                "El cliente ha sido {$estadoString} exitosamente."
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('El cliente no existe o no es válido para esta operación.', 404);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cambiar el estado del cliente');
        }
    }
}