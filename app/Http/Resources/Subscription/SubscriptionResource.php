<?php

namespace App\Http\Resources\Subscription;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'account_id' => $this->id,
            'nombre_empresa' => $this->name,
            'plan_actual' => $this->plan_type ? [
                'tipo' => $this->plan_type,
                'limite_productos' => $this->max_products,
            ] : null,
            'estado_suscripcion' => $this->subscription_status,
            'vence_en' => $this->subscription_ends_at?->toIso8601String(),
            'fecha_actualizacion' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}