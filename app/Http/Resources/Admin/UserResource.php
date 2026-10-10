<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
// Reutilizamos el BranchResource que creaste en el paso anterior
use App\Http\Resources\Admin\BranchResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'nombre'         => $this->name,
            'email'          => $this->email,
            'estado'         => $this->is_active ? 'Activo' : 'Inactivo',
            'fecha_registro' => $this->created_at->toDateTimeString(),
            
            // Evaluamos la relación hacia arriba (Sucursal -> Cuenta -> Plan -> Productos)
            // Usamos whenLoaded para evitar errores si la relación viene vacía
            'sucursal_asignada' => new BranchResource($this->whenLoaded('branch')),
        ];
    }
}