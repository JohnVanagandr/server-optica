<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\LikeAnalyticsService;
use App\Http\Resources\Product\ProductMetricResource;
use Illuminate\Http\JsonResponse;

class LikeAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(LikeAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function getStats(): JsonResponse
    {
        try {
            // Obtenemos los productos paginados con sus withCount calculados en el servicio
            $stats = $this->analyticsService->getPaginatedStats(15);

            return response()->json([
                'status' => 'success',
                'message' => 'Métricas de tendencias cargadas exitosamente',
                'data' => ProductMetricResource::collection($stats),
                'pagination' => [
                    'total' => $stats->total(),
                    'per_page' => $stats->perPage(),
                    'current_page' => $stats->currentPage(),
                    'last_page' => $stats->lastPage(),
                    'has_more' => $stats->hasMorePages(),
                ]
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al consultar métricas de tendencias');
        }
    }
}