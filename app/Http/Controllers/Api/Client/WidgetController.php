<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\PublishWidgetRequest;
use App\Http\Resources\Widget\WidgetResource;
use App\Http\Resources\Product\ProductResource;
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
     * Publicar o actualizar el widget y sus productos asociados.
     */
    public function publish(PublishWidgetRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Delegamos al servicio centralizado
            $widget = $this->widgetService->publishWidget(
                $user,
                $validated['config'] ?? null,
                $validated['products']
            );

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
     * Obtener el catálogo y la configuración del widget del cliente.
     */
    public function catalog(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $this->widgetService->getClientCatalogData($user);

            return $this->success([
                'widget' => $data['widget'] ? new WidgetResource($data['widget']) : null,
                'products' => ProductResource::collection($data['products'])
            ], 'Catálogo del cliente obtenido con éxito');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cargar el catálogo');
        }
    }
}