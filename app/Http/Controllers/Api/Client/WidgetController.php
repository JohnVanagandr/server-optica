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

    /**
     * Inyección de dependencias. 
     * Mantenemos el controlador limpio delegando la lógica pesada al WidgetService.
     */
    public function __construct(WidgetService $widgetService)
    {
        $this->widgetService = $widgetService;
    }

    /**
     * 🚀 PUBLICAR O ACTUALIZAR EL WIDGET (Catálogo Oficial)
     * Recibe la configuración visual y el arreglo estricto de IDs desde el frontend.
     */
    public function publish(PublishWidgetRequest $request): JsonResponse
    {
        try {
            // 1. Cargamos al usuario y su plan para validar límites
            $user = $request->user()->load('plan'); 
            $validated = $request->validated();
            
            // Extraemos los productos que el frontend estructuró como [{id: 1}, {id: 2}]
            $productsToPublish = $validated['products'] ?? [];
            $requestedCount = count($productsToPublish);

            // Validamos contra el plan del usuario (Si es -1 es ilimitado)
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

            // 2. EJECUCIÓN DEL SERVICIO: Guarda la configuración del widget y vincula los productos en product_user
            $widget = $this->widgetService->publishWidget(
                $user,
                $validated['config'] ?? null,
                $productsToPublish
            );

            // 3. ANALÍTICAS (TRAZABILIDAD): Registramos qué productos pasaron a ser oficiales
            // Extraemos solo los IDs numéricos del arreglo asociativo
            $productIds = array_column($productsToPublish, 'id'); 
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

            // 4. LIMPIEZA DEL BORRADOR: Como los productos ya son oficiales, vaciamos el carrito híbrido local
            $user->draftCartProducts()->detach();

            $message = $request->boolean('isUpdate') 
                ? '¡Catálogo actualizado correctamente!' 
                : '¡Widget publicado exitosamente!';

            // 🚀 5. LA MEJORA DEFINITIVA: Recuperación del Estado Fresco
            // En lugar de hacer $widget->load('products') (lo cual fallaba porque la relación está en el User),
            // reutilizamos el método central del servicio que sabe exactamente cómo reconstruir el catálogo.
            $data = $this->widgetService->getClientCatalogData($user);

            // Devolvemos la respuesta exacta que espera Zustand en React (Cero peticiones extras en frontend)
            return $this->success([
                'widget' => new WidgetResource($data['widget']),
                'products' => ProductResource::collection($data['products'])
            ], $message);

        } catch (InvalidArgumentException $e) {
            // Captura de errores de negocio (Ej: Reglas del servicio que no se cumplen)
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            // Captura de errores fatales del servidor
            return $this->handleException($e, 'Error al publicar la configuración del widget');
        }
    }

    /**
     * OBTENER EL ESTADO INICIAL DEL CATÁLOGO
     * Carga lo que ya está publicado y lo que el usuario dejó abandonado en el borrador.
     */
    public function catalog(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('plan'); 
            
            // Datos consolidados desde el servicio (Los verdes / guardados)
            $data = $this->widgetService->getClientCatalogData($user); 

            // Datos temporales (Los azules / pending_add del carrito)
            $draftProducts = $user->draftCartProducts()->get();

            $selectedCount = count($data['products']);
            $planLimit = $user->plan->max_products ?? 5; 
            
            return $this->success([
                'widget' => $data['widget'] ? new WidgetResource($data['widget']) : null,
                'products' => ProductResource::collection($data['products']), 
                'draft_products' => ProductResource::collection($draftProducts), 
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
     * Alterna un producto dentro del carrito borrador en tiempo real.
     * Si no está, lo agrega (attach), si está, lo quita (detach).
     */
    public function toggleCatalogProduct(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $user = $request->user();
            
            $result = $user->draftCartProducts()->toggle($request->product_id);

            // Verificamos si el ID resultante quedó adjuntado para enviar el mensaje correcto
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
     * Sincroniza las intenciones (carrito y favoritos) de un usuario que navegaba
     * como invitado (localStorage) al momento de iniciar sesión.
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

            // syncWithoutDetaching garantiza que no borremos lo que el usuario ya tenía guardado de sesiones previas
            if (!empty($request->widget_ids)) {
                $user->draftCartProducts()->syncWithoutDetaching($request->widget_ids);
            }
            if (!empty($request->likes_ids)) {
                $user->favoriteProducts()->syncWithoutDetaching($request->likes_ids);
            }

            // Retornamos el estado completo para que el frontend lo cargue en Zustand
            $draftProducts = $user->draftCartProducts()->get();
            $favoriteProducts = $user->favoriteProducts()->get();

            return $this->success([
                'draft_cart' => ProductResource::collection($draftProducts),
                'likes' => ProductResource::collection($favoriteProducts)
            ], 'Carrito sincronizado con éxito');

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al sincronizar el carrito');
        }
    }
}