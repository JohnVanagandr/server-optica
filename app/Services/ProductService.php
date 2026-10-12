<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function createProduct(array $data, ?UploadedFile $imageFile = null): Product
    {
        if ($imageFile) {
            $data['image_path'] = $imageFile->store('products', 'public');
        }

        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_featured'] = isset($data['is_featured']) ? filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN) : false;

        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data, ?UploadedFile $newImage = null): Product
    {
        if ($newImage) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $newImage->store('products', 'public');
        }

        if (isset($data['is_featured'])) {
            $data['is_featured'] = filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN);
        }

        $product->update($data);
        return $product;
    }  

    /**
     * Obtiene el catálogo de productos con filtros dinámicos y contexto del usuario.
     */
    public function getCatalogForUser(array $filters = [])
    {
        $userId = auth('sanctum')->id(); 
        
        $query = Product::with('category'); 

        if ($userId) {
            $query->withExists([
                // 1. ¿Está en favoritos (Me gusta)?
                'favoritedByUsers' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
                // 2. ¿Está en el carrito / borrador?
                'cartDraftByUsers' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
                // 3. ¿Está publicado en el catálogo oficial (Widget)?
                'publishedWidgetByUsers' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ]);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN));
        }

        $query->where('is_active', true);

        return $query->get();
    }
    
    /**
     * Obtiene los detalles de un producto para vistas públicas evaluando también su estado.
     */
    public function getPublicProductById(Product $product): Product
    {
        $userId = auth('sanctum')->id();
        $query = Product::with('category')->where('is_active', true);

        if ($userId) {
            $query->withExists([
                'favoritedByUsers' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
                'cartDraftByUsers' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
                'publishedWidgetByUsers' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ]);
        }

        return $query->findOrFail($product->id);
    } 
    
    public function deleteProduct(Product $product): bool
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
        return $product->delete();
    }    
}