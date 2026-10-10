<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductLikeController extends Controller
{
    /**
     * Alternar el estado de "Like" sobre un producto.
     */
    public function toggle(Request $request, string $productId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            $user = $request->user();

            // Ejecuta la inserción o eliminación en la tabla product_user_like
            $changes = $user->likedProducts()->toggle($product->id);

            // Analiza la matriz de cambios devuelta por Eloquent para determinar la acción final
            $isLiked = count($changes['attached']) > 0;
            $message = $isLiked ? 'Producto agregado a favoritos.' : 'Producto eliminado de favoritos.';

            return $this->success([
                'product_id' => $product->id,
                'is_liked'   => $isLiked
            ], $message);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('El producto solicitado no existe.', 404);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al procesar la interacción con el producto');
        }
    }
}