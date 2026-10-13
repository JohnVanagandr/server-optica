<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Lista todos los pagos procesados en la plataforma.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Iniciamos la consulta trayendo las relaciones necesarias para la tabla del frontend
        $query = Payment::with(['user:id,name,email', 'plan:id,name']);

        // 2. Si el administrador escribió algo en el buscador, filtramos
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                // Buscar por la referencia exacta o ID de Wompi
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                  // O buscar dentro de la relación del usuario (nombre o correo)
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // 3. Ordenamos por los más recientes y paginamos (15 por página es el estándar de Laravel)
        $payments = $query->latest()->paginate(15);

        // Retornamos directamente el paginador de Laravel, el cual ya trae la data y meta info
        return response()->json($payments);
    }
}