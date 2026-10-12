<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LikeAnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeLikes = $this->likes_count ?? 0;
        $conversions = $this->conversions_count ?? 0;
        
        $historical = $this->historical_likes_count ?? 0;
        $totalInteractions = max($historical, $activeLikes + $conversions);
        
        // Calculamos la tasa de conversión
        $conversionRate = $totalInteractions > 0 
            ? round(($conversions / $totalInteractions) * 100) 
            : 0;

        return [
            'id' => $this->id,
            'name' => $this->name ?? $this->nombre,
            'brand' => $this->brand ?? $this->marca,
            'image_url' => $this->image_url,
            'metrics' => [
                'total_likes' => $totalInteractions,
                'active_likes' => $activeLikes,
                'conversions' => $conversions,
                'conversion_rate' => $conversionRate,
            ]
        ];
    }
}