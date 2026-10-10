<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Product\ProductResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_sucursal' => $this->name,
            'ubicacion' => $this->location,
            'fecha_creacion' => $this->created_at->toDateTimeString(),
            
            // Datos de la empresa matriz y su plan (Protegido con null safe por si hay inconsistencias)
            'empresa' => [
                'id' => $this->account->id ?? null,
                'nombre' => $this->account->name ?? null,
                'plan_actual' => $this->account->plan_type ?? 'Sin plan asignado',
                'limite_productos' => $this->account->max_products ?? 0,
            ],

            // Mapeamos los usuarios asociados a esta sucursal
            'usuarios' => $this->whenLoaded('users', function () {
                return $this->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nombre' => $user->name,
                        'email' => $user->email,
                    ];
                });
            }),

            // Reutilizamos el recurso de productos para el catálogo del widget
            'productos_widget' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}