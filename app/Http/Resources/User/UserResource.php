<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $plan = $this->plan; 

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'role'        => $this->getRoleNames()->first() ?? 'client',
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'is_active'   => $this->is_active,
            
            'has_active_subscription' => (bool) $this->subscription_active,
            'subscription_ends_at'    => $this->subscription_ends_at?->toIso8601String(),

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

            // 🚀 Inyectamos de forma segura los conteos del controlador
            'wishlist_count' => $this->wishlist_count ?? 0,
            'cart_count'     => $this->cart_count ?? 0,
            'widget_count'   => $this->widget_count ?? 0,
        ];
    }
}