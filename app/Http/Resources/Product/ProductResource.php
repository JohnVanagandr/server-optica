<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Category\CategoryResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            // 1. Agregamos Brand
            'brand' => $this->brand, 
            'base_price' => (float) $this->base_price,
            'ar_model_url' => $this->ar_model_url,
            // 2. Agregamos el Web Tracking URL
            'web_tracking_url' => $this->web_tracking_url, 
            
            'image_path' => $this->image_path ? asset(Storage::url($this->image_path)) : null,
            
            'is_active' => (bool) $this->is_active,
            // 3. Agregamos el estado de Destacado
            'is_featured' => (bool) $this->is_featured, 
            
            // 4. CRÍTICO: Exponer el category_id crudo para que el combobox del frontend lo pueda seleccionar
            'category_id' => $this->category_id, 
            
            'category' => new CategoryResource($this->whenLoaded('category')),
            'likes_count' => $this->whenCounted('likes'),
        ];
    }
}