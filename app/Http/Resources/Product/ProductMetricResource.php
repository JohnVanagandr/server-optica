<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductMetricResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. Extraemos los valores base
        $totalLikes = (int) ($this->total_likes ?? 0);
        $activeLikes = (int) ($this->active_likes ?? 0);
        $inCart = (int) ($this->in_cart_count ?? 0);
        $inWidget = (int) ($this->in_widget_count ?? 0);
        $cartMoves = (int) ($this->cart_moves_count ?? 0);
        $conversions = (int) ($this->conversions ?? 0);

        // 🚀 2. CORRECCIÓN DE LA DISONANCIA MATEMÁTICA
        
        // El éxito real es si el producto está actualmente en el carrito o ya publicado
        $currentSuccess = $inCart + $inWidget;
        
        // La base del embudo toma el mayor valor. 
        // Si hay 0 intenciones pero 1 éxito (agregado directo), la base será 1.
        $funnelBase = max($totalLikes, $currentSuccess);

        // Calculamos el porcentaje real, imposible de desbordar
        $conversionRate = $funnelBase > 0 
            ? round(($currentSuccess / $funnelBase) * 100) 
            : 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'image_path' => $this->image_path ? asset(Storage::url($this->image_path)) : null,
            'image_url' => $this->image_url ?? ($this->image_path ? asset(Storage::url($this->image_path)) : null),
            'metrics' => [
                'total_likes' => $totalLikes,
                'active_likes' => $activeLikes,
                'in_cart' => $inCart,
                'in_widget' => $inWidget,
                'cart_moves' => $cartMoves,
                'conversions' => $conversions,
                'conversion_rate' => $conversionRate,
            ]
        ];
    }
}