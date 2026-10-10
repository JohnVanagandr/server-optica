<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 🎯 Usamos $this->plan directamente para que Laravel lo busque automáticamente si el controlador olvidó cargarlo
        $plan = $this->plan; 

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'role'        => $this->getRoleNames()->first() ?? 'client',
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'is_active'   => $this->is_active,
            
            // Banderas
            'has_active_subscription' => (bool) $this->subscription_active,
            'subscription_ends_at'    => $this->subscription_ends_at?->toIso8601String(),

            // Objeto Plan
            'plan' => $plan ? [
                'id'           => $plan->id,
                'type'         => $plan->type,
                'name'         => $plan->name,
                'price'        => $plan->price,
                'max_products' => $plan->max_products,
                'max_widgets'  => $plan->max_widgets,
                'description'  => $plan->description,
                'whatsapp'     => $plan->whatsapp,
            ] : null,
        ];
    }
}