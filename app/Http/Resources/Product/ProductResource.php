<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Category\CategoryResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'brand' => $this->brand,
            'base_price' => (float) $this->base_price,
            'ar_model_url' => $this->ar_model_url,
            'web_tracking_url' => $this->web_tracking_url, 
            
            'image_path' => $this->image_path ? asset(Storage::url($this->image_path)) : null,
            'image_url' => $this->image_url ?? ($this->image_path ? asset(Storage::url($this->image_path)) : null),
            
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured, 
            'category_id' => $this->category_id, 
            
            // Banderas separadas e independientes
            'is_favorito' => $this->when(
                isset($this->favorited_by_users_exists), 
                (bool) $this->favorited_by_users_exists, 
                false
            ),
            
            'is_en_cart' => $this->when(
                isset($this->cart_draft_by_users_exists), 
                (bool) $this->cart_draft_by_users_exists, 
                false
            ),
            
            'is_en_widget' => $this->when(
                isset($this->published_widget_by_users_exists), 
                (bool) $this->published_widget_by_users_exists, 
                false
            ),
            
            'category' => new CategoryResource($this->whenLoaded('category')),
            'likes_count' => $this->whenCounted('likes'),
        ];
    }
}