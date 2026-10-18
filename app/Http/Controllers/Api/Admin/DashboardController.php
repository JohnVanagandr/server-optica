<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use App\Models\Widget;
use App\Models\Payment;

class DashboardController extends Controller
{
    /**
     * Obtiene las métricas generales para el dashboard administrativo.
     */
    public function getMetrics(Request $request)
    {
        try {
            // 1. Clientes (Ópticas) Totales
            // ✅ CORREGIDO: Usamos el scope real de Spatie y el nombre de rol exacto ('client')
            $totalClients = User::role('client')->count();

            // 2. Ingresos del Mes (MRR)
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            
            $monthlyRevenue = Payment::where('status', 'APPROVED')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('amount'); 

            // 3. Total de Monturas
            $totalProducts = Product::count();

            // 4. Widgets Activos
            $activeWidgets = Widget::count();

            // 5. Actividad Reciente (Últimos 3 clientes registrados)
            // ✅ CORREGIDO: Filtramos por rol 'client' directamente
            $recentActivity = User::role('client')
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get()
                ->map(function ($user) {
                    return [
                        'title' => 'Nuevo registro de óptica',
                        'description' => $user->name . ' se ha unido a la plataforma.',
                        'time_ago' => $user->created_at->diffForHumans()
                    ];
                });

            // Retornamos el JSON con el formato ApiResponse
            return response()->json([
                'success' => true,
                'data' => [
                    'total_clients' => $totalClients,
                    'monthly_revenue' => (float) $monthlyRevenue,
                    'total_products' => $totalProducts,
                    'active_widgets' => $activeWidgets,
                    'recent_activity' => $recentActivity
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular las métricas del sistema.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}