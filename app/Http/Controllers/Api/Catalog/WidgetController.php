<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\StoreWidgetRequest;
use App\Models\Branch;
use App\Models\EmbedWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WidgetController extends Controller
{
    /**
     * Obtiene el listado de todos los widgets configurados por la sucursal del cliente.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $branch = $request->user()->branch;

            $widgets = $branch->embedWidgets()->latest()->get();

            $data = $widgets->map(function ($widget) {
                return [
                    'id'            => $widget->id,
                    'name'          => $widget->name,
                    'configuracion' => $widget->settings,
                    'creado_el'     => $widget->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return $this->success($data, 'Listado de catálogos obtenido con éxito.');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error al listar los catálogos.');
        }
    }

    /**
     * Crear un nuevo Widget independiente.
     */
    public function store(StoreWidgetRequest $request): JsonResponse
    {
        try {
            $branch = $request->user()->branch;
            $account = $branch->account;

            // 1. Validar límite de creación de catálogos del plan
            $this->validateWidgetCreationLimit($account, $branch);

            // 2. Validar límite global de productos
            $this->validateGlobalProductQuota($account, $branch, count($request->product_ids));

            // 3. Crear el registro
            $widget = $branch->embedWidgets()->create([
                'name'     => $request->input('settings.title', 'Nuevo Catálogo Vía API'),
                'settings' => $request->settings
            ]);

            // 4. Sincronizar productos estrictamente al WIDGET (Tabla pivote embed_widget_product)
            $widget->products()->sync($request->product_ids);

            return $this->success([
                'widget_id'     => $widget->id,
                'configuracion' => $widget->settings
            ], 'Widget creado con éxito.', 201);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error al crear el Widget.');
        }
    }

    /**
     * Actualizar un widget específico usando su ID.
     */
    public function update(StoreWidgetRequest $request, string $widgetId): JsonResponse
    {
        try {
            $branch = $request->user()->branch;
            $account = $branch->account;

            // 1. Validar límite global excluyendo el consumo del widget actual
            $this->validateGlobalProductQuota($account, $branch, count($request->product_ids), $widgetId);

            $widget = $branch->embedWidgets()->findOrFail($widgetId);

            // 2. Actualizar configuración
            $widget->update([
                'name'     => $request->input('settings.title', $widget->name),
                'settings' => $request->settings
            ]);

            // 3. Resincronizar productos en la tabla pivote
            $widget->products()->sync($request->product_ids);

            return $this->success([
                'widget_id'     => $widget->id,
                'configuracion' => $widget->settings
            ], 'Widget actualizado con éxito.');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error al actualizar el Widget.');
        }
    }

    /**
     * Eliminar un widget específico y liberar el espacio.
     */
    public function destroy(Request $request, string $widgetId): JsonResponse
    {
        try {
            $branch = $request->user()->branch;
            
            $widget = $branch->embedWidgets()->findOrFail($widgetId);

            // Purgar integridad referencial en la tabla pivote antes de destruir
            $widget->products()->detach();

            $widget->delete();

            return $this->success(null, 'Widget eliminado correctamente. Cupo liberado.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('El widget no existe o no tiene autorización para eliminarlo.', 404);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error al eliminar el widget.');
        }
    }

    /**
     * Obtiene los datos públicos de un widget (Para consumo JSON desde el Frontend)
     */
    public function show(string $widgetId): JsonResponse
    {
        try {
            // Eager loading de los productos y sus categorías
            $widget = EmbedWidget::with(['branch', 'products.category'])->findOrFail($widgetId);

            return $this->success([
                'id'            => $widget->id,
                'nombre'        => $widget->name,
                'configuracion' => $widget->settings,
                'empresa'       => [
                    'nombre' => $widget->branch->name,
                    // Aquí puedes añadir el logo de la óptica u otros datos públicos seguros
                ],
                // Si tienes un ProductResource, úsalo aquí para formatear la salida
                'productos'     => $widget->products 
            ], 'Datos del catálogo obtenidos con éxito.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('El catálogo solicitado no existe o fue eliminado.', 404);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error al cargar los datos públicos del catálogo.');
        }
    }    

    /**
     * Genera el código iframe usando el ID del widget específico.
     */
    public function embedCode(Request $request, string $widgetId): JsonResponse
    {
        $branch = $request->user()->branch;
        $widget = $branch->embedWidgets()->findOrFail($widgetId);

        $url = route('public.widget.render', ['widget' => $widget->id]);
        
        return $this->success([
            'widget_url'    => $url,
            'iframe_code'   => $this->generateIframeHtml($url),
            'instrucciones' => 'Copia este código HTML en la web donde quieras mostrar este catálogo específico.'
        ]);
    }

    /**
     * Renderiza el Iframe público usando los datos del widget apuntado.
     */
    public function renderIframe(string $widgetId)
    {
        try {
            // Eager loading aislando los productos que pertenecen únicamente a este widget
            $widget = EmbedWidget::with(['branch.account', 'products.category'])->findOrFail($widgetId);
            $branch = $widget->branch;
            
            $defaultSettings = [
                'primary_color' => '#2563eb',
                'title'         => 'Nuestro Catálogo',
                'show_prices'   => true
            ];

            $settings = array_merge($defaultSettings, $widget->settings ?? []);

            return view('widget.iframe', [
                'branch'    => $branch,
                'productos' => $widget->products, // Inyección estricta desde la relación local
                'settings'  => $settings
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response('<h1 style="font-family:sans-serif;text-align:center;color:#666;">Widget no encontrado</h1>', 404);
        } catch (\Exception $e) {
            Log::error('Error renderizando Iframe: ' . $e->getMessage());
            return response('<h1 style="font-family:sans-serif;text-align:center;color:#e3342f;">Error interno al cargar el catálogo</h1>', 500);
        }
    }

    // --- Métodos Privados de Soporte ---

    private function validateWidgetCreationLimit($account, $branch): void
    {
        // Límite de catálogos resuelto directamente desde Account::PLAN_LIMITS (sin tabla maestra)
        $limiteWidgets = $account->max_widgets ?? 1; // Fallback a 1 si no tiene plan_type asignado

        if ($branch->embedWidgets()->count() >= $limiteWidgets) {
            throw new \Exception("Límite de catálogos alcanzado para tu plan (Máx: {$limiteWidgets}).", 422);
        }
    }

    private function validateGlobalProductQuota($account, $branch, int $requestedCount, ?string $currentWidgetId = null): void
    {
        $maxProducts = $account->max_products ?? 0;

        if ($maxProducts <= 0) {
            throw new \Exception('Plan insuficiente para operar widgets.', 403);
        }

        $query = $branch->embedWidgets();

        if ($currentWidgetId) {
            $query->where('id', '!=', $currentWidgetId);
        }

        $productosEnUso = $query->withCount('products')->get()->sum('products_count');
        $consumoProyectado = $productosEnUso + $requestedCount;

        if ($consumoProyectado > $maxProducts) {
            $disponible = $maxProducts - $productosEnUso;
            throw new \Exception("Límite global excedido. Tienes {$disponible} cupos disponibles distribuidos para catálogos.", 422);
        }
    }

    private function generateIframeHtml(string $url): string
    {
        return '<iframe src="' . $url . '" width="100%" height="700px" style="border:none;" allow="xr-spatial-tracking; fullscreen"></iframe>';
    }

    private function handleError(\Exception $e, string $customMessage): JsonResponse
    {
        Log::error("{$customMessage}: " . $e->getMessage());
        $code = in_array($e->getCode(), [403, 404, 422]) ? $e->getCode() : 500;
        return $this->error($code === 500 ? 'Error interno del servidor.' : $e->getMessage(), $code);
    }
}