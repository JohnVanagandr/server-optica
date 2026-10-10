<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $account = $this->branch?->account;

        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'email' => $this->email,
            'rol' => $this->roles->pluck('name')->first(), 
            
            // Evaluamos si tiene sucursal antes de intentar leer sus propiedades
            'sucursal' => $this->branch ? [
                'id' => $this->branch->id,
                'nombre' => $this->branch->name,
            ] : null,
            
            // Evaluamos si tiene cuenta (a través de la sucursal)
            'cuenta' => $account ? [
                'id' => $account->id,
                'nombre_empresa' => $account->name,
                // Plan simplificado: sin tabla maestra/pivote, límite resuelto por plan_type
                'plan' => $account->plan_type ? [
                    'tipo' => $account->plan_type,
                    'estado' => $account->subscription_status,
                    'limite_productos' => $account->max_products,
                    'vence_en' => $account->subscription_ends_at?->toIso8601String(),
                ] : null,
            ] : null
        ];
    }
}