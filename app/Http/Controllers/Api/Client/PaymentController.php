<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Plan;
use App\Models\Payment;
use App\Services\WompiService;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected WompiService $wompiService;

    // Inyectamos el servicio de Wompi que creamos en el paso anterior
    public function __construct(WompiService $wompiService)
    {
        $this->wompiService = $wompiService;
    }

    /**
     * Inicializa la transacción y devuelve los datos firmados para el Frontend
     */
    public function initPayment(Request $request): JsonResponse
    {
        // 1. Validamos que nos envíen un plan válido
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        try {
            $user = $request->user();
            
            // Suponiendo que tu modelo Plan tiene un campo 'price'
            $plan = Plan::findOrFail($request->plan_id); 
            
            // Si el plan es gratuito (precio 0), no deberíamos pasarlo por Wompi
            if ($plan->price <= 0) {
                return $this->error('Este plan no requiere pago procesado por Wompi.', 400);
            }

            // 2. Generamos la referencia única de pago
            $reference = $this->wompiService->generateReference();

            // 3. Creamos el registro del pago en la BD asegurado con una transacción
            $payment = DB::transaction(function () use ($user, $plan, $reference) {
                return Payment::create([
                    'user_id'   => $user->id,
                    'plan_id'   => $plan->id,
                    'reference' => $reference,
                    'amount'    => $plan->price,
                    'currency'  => 'COP',
                    'status'    => 'PENDING',
                ]);
            });

            // 4. Obtenemos los datos cifrados desde nuestro WompiService
            $checkoutData = $this->wompiService->getCheckoutData($reference, $plan->price);

            return $this->success(
                $checkoutData, 
                'Transacción inicializada correctamente. Listo para abrir Wompi.'
            );

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al inicializar el pago con Wompi');
        }
    }
}