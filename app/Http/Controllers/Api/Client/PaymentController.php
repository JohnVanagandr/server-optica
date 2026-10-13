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
     * Devuelve el pago pendiente del usuario (si tiene uno)
     */
    public function currentPayment(Request $request): JsonResponse
    {
        $latestPayment = Payment::with('plan:id,name')
                                ->where('user_id', $request->user()->id)
                                ->where('status', 'PENDING')
                                ->latest()
                                ->first();
        return $this->success($latestPayment, 'Estado actual del pago');
    }

    /**
     * Inicializa la transacción y devuelve los datos firmados para el Frontend
     */
    public function initPayment(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        try {
            $user = $request->user();
            $plan = Plan::findOrFail($request->plan_id); 
            
            if ($plan->price <= 0) {
                return $this->error('Este plan no requiere pago procesado por Wompi.', 400);
            }

            $reference = $this->wompiService->generateReference();

            $payment = DB::transaction(function () use ($user, $plan, $reference) {
                
                $existingPayment = Payment::where('user_id', $user->id)
                                          ->where('status', 'PENDING')
                                          ->first();

                if ($existingPayment) {
                    $existingPayment->update([
                        'plan_id'   => $plan->id,
                        'reference' => $reference,
                        'amount'    => $plan->price,
                    ]);
                    return $existingPayment;
                }

                return Payment::create([
                    'user_id'   => $user->id,
                    'plan_id'   => $plan->id,
                    'reference' => $reference,
                    'amount'    => $plan->price,
                    'currency'  => 'COP',
                    'status'    => 'PENDING',
                ]);
            });

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