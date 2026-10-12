<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductMetricResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalLikes = (int) ($this->total_likes ?? 0);
        $conversions = (int) ($this->conversions ?? 0);
        
        $conversionRate = $totalLikes > 0 
            ? round(($conversions / $totalLikes) * 100, 2) 
            : 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'image_path' => $this->image_path ? asset(Storage::url($this->image_path)) : null,
            'image_url' => $this->image_url ?? ($this->image_path ? asset(Storage::url($this->image_path)) : null),
            'metrics' => [
                'total_likes' => $totalLikes,                       // Interés total histórico ('liked')
                'active_likes' => (int) ($this->active_likes ?? 0),   // Me gusta / En espera actuales
                'in_cart' => (int) ($this->in_cart_count ?? 0),         // En carro (borrador actual)
                'in_widget' => (int) ($this->in_widget_count ?? 0),     // En catálogo (publicado actual)
                'cart_moves' => (int) ($this->cart_moves_count ?? 0),   // Historial de pasos al carro
                'conversions' => $conversions,                          // Total de conversiones a widget
                'conversion_rate' => $conversionRate,
            ]
        ];
    }
}