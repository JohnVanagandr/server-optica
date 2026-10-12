<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\PublishWidgetRequest;
use App\Http\Resources\Widget\WidgetResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\ProductInteraction;
use App\Services\WidgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class WidgetController extends Controller
{
    protected $widgetService;

    public function __construct(WidgetService $widgetService)
    {
        $this->widgetService = $widgetService;
    }

    /**
     * Publica un widget con los productos y configuración proporcionados.
     */
    public function publish(PublishWidgetRequest $request): JsonResponse
    {
        try {
            $user = $request->user()->load('plan'); 
            $validated = $request->validated();
            
            $productsToPublish = $validated['products'] ?? [];
            $requestedCount = count($productsToPublish);

            $planLimit = $user->plan->max_products ?? 5;

            if ($planLimit != -1 && $requestedCount > $planLimit) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Tu plan actual ({$user->plan->name}) solo permite un máximo de {$planLimit} monturas en el catálogo. Intenta publicar {$requestedCount}.",
                    'data' => [
                        'limit' => $planLimit,
                        'requested' => $requestedCount
                    ]
                ], 403);
            }

            // 1. Publicamos usando el servicio (se guarda en product_user)
            $widget = $this->widgetService->publishWidget(
                $user,
                $validated['config'] ?? null,
                $productsToPublish
            );

            // 2. 🚀 REGISTRO DE TRAZA PARA ANALÍTICAS: Registramos que pasaron al catálogo oficial
            $productIds = array_column($productsToPublish, 'id'); // o collect($productsToPublish)->pluck('id')
            foreach ($productsToPublish as $prod) {
                $prodId = is_array($prod) ? ($prod['id'] ?? null) : $prod;
                if ($prodId) {
                    ProductInteraction::create([
                        'user_id' => $user->id,
                        'product_id' => $prodId,
                        'action' => 'added_to_widget'
                    ]);
                }
            }

            // 3. ELIMINAMOS EL CARRITO BORRADOR (La intención se convirtió en acción)
            $user->draftCartProducts()->detach();

            $message = $request->boolean('isUpdate') 
                ? '¡Catálogo actualizado correctamente!' 
                : '¡Widget publicado exitosamente!';

            return $this->success(
                new WidgetResource($widget),
                $message
            );

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al publicar la configuración del widget');
        }
    }

    /**
     * Obtener el catálogo (el publicado en el widget) y el carrito borrador (abandonado).
     */
    public function catalog(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('plan'); 
            
            // Traemos la data del widget (Tabla product_user)
            $data = $this->widgetService->getClientCatalogData($user); 

            // 🚀 Traemos la data del borrador (Tabla cart_user)
            $draftProducts = $user->draftCartProducts()->get();

            $selectedCount = count($data['products']);
            $planLimit = $user->plan->max_products ?? 5; 
            
            return $this->success([
                'widget' => $data['widget'] ? new WidgetResource($data['widget']) : null,
                'products' => ProductResource::collection($data['products']), // Lo publicado
                'draft_products' => ProductResource::collection($draftProducts), // El borrador temporal
                'plan_status' => [
                    'limit' => $planLimit,
                    'selected' => $selectedCount,
                ]
            ], 'Catálogo y carrito obtenidos con éxito');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cargar el catálogo');
        }
    }

    /**
     * =================================================================
     * MÉTODOS PARA EL CARRO HÍBRIDO Y SIMULADOR
     * =================================================================
     */

    /**
     * Alterna (agrega o quita) un producto del CARRITO (Borrador).
     */
    public function toggleCatalogProduct(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $user = $request->user();
            
            // 🚀 CAMBIO VITAL: Usamos draftCartProducts() para guardarlo en la tabla 'cart_user'
            $result = $user->draftCartProducts()->toggle($request->product_id);

            $attached = count($result['attached']) > 0;
            $message = $attached ? 'Añadido al carrito exitosamente' : 'Removido del carrito';

            return $this->success([
                'is_in_cart' => $attached
            ], $message);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al modificar el carrito');
        }
    }

    /**
     * Sincroniza el estado del carrito de invitado guardándolo como Borrador.
     */
    public function syncState(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'widget_ids' => 'nullable|array',
                'widget_ids.*' => 'integer|exists:products,id',
                'likes_ids' => 'nullable|array',
                'likes_ids.*' => 'integer|exists:products,id'
            ]);

            $user = $request->user();

            // 1. Fusión en la Base de Datos (Respeta lo que ya tenía guardado)
            if (!empty($request->widget_ids)) {
                $user->draftCartProducts()->syncWithoutDetaching($request->widget_ids);
            }
            if (!empty($request->likes_ids)) {
                $user->favoriteProducts()->syncWithoutDetaching($request->likes_ids);
            }

            // 2. 🚀 EL CAMBIO: Traemos los objetos completos con todos sus datos
            $draftProducts = $user->draftCartProducts()->get();
            $favoriteProducts = $user->favoriteProducts()->get();

            return $this->success([
                // Usamos ProductResource para devolver las imágenes y precios formateados
                'draft_cart' => ProductResource::collection($draftProducts),
                'likes' => ProductResource::collection($favoriteProducts)
            ], 'Carrito sincronizado con éxito');

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al sincronizar el carrito');
        }
    }
}