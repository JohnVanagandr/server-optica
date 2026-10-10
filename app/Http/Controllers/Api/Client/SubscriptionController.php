<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Obtener el catálogo de planes disponibles desde la base de datos.
     */
    public function index(): JsonResponse
    {
        $plans = Plan::all();

        return $this->success($plans, 'Planes obtenidos correctamente');
    }

    /**
     * Procesa la suscripción/pago relacionando el plan_id directamente al usuario.
     */
    public function subscribe(StoreSubscriptionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Validamos que el usuario esté activo
        if (!$user->is_active) {
            return $this->error('Tu cuenta se encuentra inactiva. No puedes adquirir un plan.', 403);
        }

        // Asignamos el plan directamente al usuario
        $user->plan_id = $validated['plan_id'];
        $user->subscription_active = true;
        $user->subscription_ends_at = now()->addMonth(); // Simulación de vigencia mensual
        $user->save();

        // Cargamos la relación del plan para retornarla en la respuesta
        $user->load('plan');

        return $this->success(
            [
                'plan_name'            => $user->plan?->name,
                'subscription_active'  => $user->subscription_active,
                'max_products'         => $user->plan?->max_products,
                'max_widgets'          => $user->plan?->max_widgets,
                'subscription_ends_at' => $user->subscription_ends_at?->toIso8601String(),
            ],
            '¡Pago procesado con éxito! Tu plan ya está activo.'
        );
    }
}