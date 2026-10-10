<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Crea un nuevo producto respetando las selecciones del usuario.
     */
    public function createProduct(array $data, ?UploadedFile $imageFile = null): Product
    {
        // 1. Manejo del archivo de imagen
        if ($imageFile) {
            $data['image_path'] = $imageFile->store('products', 'public');
        }

        // 2. Seguridad en booleanos y valores por defecto
        // Aseguramos que un producto nuevo siempre nazca "activo" por defecto,
        // a menos que el frontend envíe explícitamente lo contrario.
        $data['is_active'] = $data['is_active'] ?? true;
        
        // Convertimos a booleano real el valor recibido, si no viene, será false.
        $data['is_featured'] = isset($data['is_featured']) ? filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN) : false;

        // 3. Creación en base de datos
        return Product::create($data);
    }

    /**
     * Actualiza un producto y reemplaza la imagen si se proporciona una nueva.
     */
    public function updateProduct(Product $product, array $data, ?UploadedFile $newImage = null): Product
    {
        // 1. Reemplazo de imagen física
        if ($newImage) {
            // Verificamos si existe una imagen anterior y la eliminamos para no saturar el servidor
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            
            // Guardamos la nueva imagen
            $data['image_path'] = $newImage->store('products', 'public');
        }

        // 2. Seguridad en el casteo del destacado (si viene en la actualización)
        if (isset($data['is_featured'])) {
            $data['is_featured'] = filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN);
        }

        // 3. Actualizamos el registro en la base de datos
        $product->update($data);

        return $product;
    }  

    /**
     * Obtiene el catálogo de productos con filtros dinámicos.
     */
    public function getCatalogForUser(array $filters = [])
    {
        $query = Product::with('category', 'likes');

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN));
        }

        // Solo listamos productos activos en el entorno público
        $query->where('is_active', true);

        return $query->get();
    }
    
    /**
     * Obtiene los detalles de un producto para vistas públicas (clientes finales).
     */
    public function getPublicProductById(Product $product): Product
    {
        return Product::with('category')
            ->where('is_active', true)
            ->findOrFail($product->id);
    }   
    
    /**
     * Elimina completamente un producto y su imagen asociada.
     */
    public function deleteProduct(Product $product): bool
    {
        // 1. Verificamos y eliminamos la imagen física
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        // 2. Eliminamos el registro de la base de datos
        return $product->delete();
    }    
}