<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Listar todos los usuarios del sistema con su jerarquía empresarial
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->integer('per_page', 10);

            // Carga ansiosa masiva para tener toda la foto del cliente en la lista
            $users = User::with([
                'branch.account', 
                'branch.products.category'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

            return $this->paginatedResponse(
                $users,
                UserResource::class,
                'Listado de usuarios obtenido con éxito.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al listar los usuarios');
        }
    }

    /**
     * Ver los datos hiper-detallados de un usuario específico
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = User::with([
                'branch.account', 
                'branch.products.category'
            ])->findOrFail($id);

            return $this->success(
                new UserResource($user),
                'Detalle completo del usuario obtenido con éxito.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('El usuario solicitado no existe.', 404);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener el usuario');
        }
    }

    /**
     * Acción de seguridad: Activar/Desactivar acceso al sistema
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $user = User::with('branch')->findOrFail($id);

            // 🛡️ Regla de Negocio Crítica: Evitar que el SuperAdmin se desactive a sí mismo
            if ($user->id === auth()->id()) {
                return $this->error('Operación denegada: No puedes desactivar tu propia cuenta de administrador.', 403);
            }

            // Alternamos el valor booleano actual (Si es true pasa a false, y viceversa)
            $user->is_active = !$user->is_active;
            $user->save();

            // Mensaje dinámico para dar mejor retroalimentación al frontend
            $estadoString = $user->is_active ? 'activado' : 'desactivado (suspendido)';

            return $this->success(
                new UserResource($user),
                "El usuario ha sido {$estadoString} exitosamente."
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('El usuario no existe.', 404);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cambiar el estado del usuario');
        }
    }
}