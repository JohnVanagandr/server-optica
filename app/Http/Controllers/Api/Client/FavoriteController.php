<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FavoriteService;
use App\Http\Resources\Product\ProductResource;
use App\Models\ProductInteraction;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{

    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * Obtiene todos los productos favoritos del usuario actual
     */
    public function index(Request $request)
    {
        try {
            $favoritos = $this->favoriteService->getFavorites($request->user());
            
            // Retornamos usando el método success() del trait
            return $this->success($favoritos, 'Lista de deseos recuperada exitosamente');
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener la lista de deseos');
        }
    }
    
    /**
     * Alterna (agrega o quita) un producto de la lista de deseos (Favoritos).
     */
    public function toggle(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $user = $request->user();
            $productId = $request->product_id;
            
            // 🚀 Usar la relación unificada favoriteProducts()
            $result = $user->favoriteProducts()->toggle($productId);

            $attached = count($result['attached']) > 0;
            
            // 🚀 REGISTRO ANALÍTICO: Solo registramos la interacción si se acaba de AÑADIR (liked)
            if ($attached) {
                \App\Models\ProductInteraction::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'action' => 'liked'
                ]);
            }

            $message = $attached ? 'Añadido a favoritos exitosamente' : 'Removido de favoritos';

            return $this->success([
                'is_favorito' => $attached
            ], $message);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al modificar la lista de deseos');
        }
    }

    public function convertFavoritesToCart(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'integer|exists:products,id'
            ]);

            $user = $request->user();
            $productIds = $request->product_ids;

            // 1. Guardamos en el carrito borrador (cart_user)
            $user->draftCartProducts()->syncWithoutDetaching($productIds);

            // 2. Eliminamos de la lista de deseos (product_user_like) tal como lo solicitaste
            $user->favoriteProducts()->detach($productIds);

            // 3. LA CLAVE DEL RASTREO: Registramos la interacción histórica en 'product_interactions'
            // para que las analíticas sepan que estos productos vinieron de un "Me Gusta"
            foreach ($productIds as $productId) {
                ProductInteraction::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'action' => 'moved_to_cart', // O 'liked_to_cart'
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Devolvemos las listas actualizadas para React
            $draftProducts = $user->draftCartProducts()->get();
            $favoriteProducts = $user->favoriteProducts()->get();

            return $this->success([
                'draft_cart' => ProductResource::collection($draftProducts),
                'likes' => ProductResource::collection($favoriteProducts)
            ], 'Productos movidos al carrito exitosamente');

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al convertir favoritos al carrito');
        }
    }   
}