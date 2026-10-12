<?php

namespace App\Services;

use App\Models\ProductInteraction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavoriteService
{
    /**
     * Obtiene todos los productos favoritos del usuario.
     */
    public function getFavorites(User $user)
    {
        return $user->favoriteProducts;
    }

    /**
     * Alterna el estado de "Me gusta" y registra la interacción.
     */
    public function toggleFavorite(User $user, int $productId): array
    {
        return DB::transaction(function () use ($user, $productId) {
            
            $exists = DB::table('likes')
                ->where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                // A. Quitar Like
                DB::table('likes')
                    ->where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->delete();

                // B. Registrar Historial Analítico
                ProductInteraction::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'action' => 'unliked'
                ]);

                return [
                    'message' => 'Producto removido de la lista de deseos.', 
                    'status' => 'unliked'
                ];
            }

            // A. Agregar Like
            DB::table('likes')->insert([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);

            // B. Registrar Historial Analítico
            ProductInteraction::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'action' => 'liked'
            ]);

            return [
                'message' => 'Producto agregado a tu lista de deseos.', 
                'status' => 'liked'
            ];
        });
    }

    /**
     * Convierte productos de la lista de deseos al catálogo (Widget)
     * Ejecuta los 3 pasos necesarios para no romper las analíticas.
     */
    public function convertToWidget(User $user, array $productIds): array
    {
        return DB::transaction(function () use ($user, $productIds) {
            
            // 1. AÑADIR AL CATÁLOGO
            $user->catalogProducts()->syncWithoutDetaching($productIds);

            // 2. REGISTRAR LA CONVERSIÓN
            $interactions = [];
            $now = now();
            
            foreach ($productIds as $productId) {
                $interactions[] = [
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'action' => 'added_to_widget',
                    'created_at' => $now
                ];
            }
            ProductInteraction::insert($interactions);

            // 3. LIMPIAR LA LISTA DE DESEOS
            DB::table('likes')
                ->where('user_id', $user->id)
                ->whereIn('product_id', $productIds)
                ->delete();

            return [
                'message' => 'Productos movidos al catálogo con éxito.',
                'status' => 'converted'
            ];
        });
    }
}