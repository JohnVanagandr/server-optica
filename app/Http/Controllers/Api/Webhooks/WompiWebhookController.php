<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class WompiWebhookController extends Controller
{
    /**
     * Endpoint que Wompi llamará asíncronamente
     */
    public function handle(Request $request)
    {
        try {
            // 1. Obtener la llave secreta de eventos del .env
            $eventsSecret = env('WOMPI_EVENTS_SECRET');
            
            // 2. Extraer los datos del evento de Wompi
            $payload = $request->all();
            
            if (!isset($payload['event']) || $payload['event'] !== 'transaction.updated') {
                return response()->json(['message' => 'Evento ignorado'], 200);
            }

            $transaction = $payload['data']['transaction'];
            $signatureObj = $payload['signature'] ?? null;
            
            // 🔥 Extraer el timestamp obligatorio del payload
            $timestamp = $payload['timestamp'] ?? ''; 

            // 3. Validar la Firma Criptográfica de Wompi (Checksum) con el timestamp incluido
            if (!$this->isValidSignature($transaction, $signatureObj, $timestamp, $eventsSecret)) {
                Log::warning('Wompi Webhook: Firma inválida detectada', ['payload' => $payload]);
                return response()->json(['error' => 'Firma inválida'], 401);
            }

            // 4. Buscar el pago en nuestra base de datos por la referencia
            $payment = Payment::where('reference', $transaction['reference'])->first();

            if (!$payment) {
                Log::error('Wompi Webhook: Referencia no encontrada', ['ref' => $transaction['reference']]);
                return response()->json(['error' => 'Pago no encontrado'], 404);
            }

            // 5. Actualizar el estado del pago (APPROVED, DECLINED, VOIDED, ERROR)
            $payment->status = $transaction['status'];
            $payment->gateway_transaction_id = $transaction['id'];
            $payment->payment_method_data = $transaction['payment_method'] ?? null;
            $payment->save();

            // 6. 🚀 SI EL PAGO FUE APROBADO, ACTIVAR LA SUSCRIPCIÓN DEL USUARIO
            if ($transaction['status'] === 'APPROVED') {
                $user = $payment->user;
                $user->plan_id = $payment->plan_id;
                
                // 🔥 ESTA ES LA LÍNEA QUE FALTABA
                $user->subscription_active = true; 
                
                // Si tienes lógica de fechas, agrégala aquí también
                // $user->subscription_ends_at = now()->addMonth(); 
                
                $user->save();

                Log::info("Suscripción activada para el usuario {$user->id} con referencia {$payment->reference}");
            }

            // 7. Responder a Wompi con un 200 OK para que no reintenten enviar el webhook
            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Error en Wompi Webhook: ' . $e->getMessage());
            // Aun si hay error interno, retornamos 200 para que Wompi no haga spam, 
            // a menos que quieras que Wompi reintente el envío.
            return response()->json(['error' => 'Internal Server Error'], 500); 
        }
    }

    /**
     * Reconstruye la firma y la compara con la enviada por Wompi
     */
    private function isValidSignature($transaction, $signatureObj, $timestamp, $eventsSecret)
    {
        if (!$signatureObj || !isset($signatureObj['checksum']) || !isset($signatureObj['properties'])) {
            return false;
        }

        // Concatenar los valores según las propiedades indicadas por Wompi
        $concatenatedValues = '';
        foreach ($signatureObj['properties'] as $property) {
            // Separa "transaction.id" en ['transaction', 'id']
            $keys = explode('.', $property); 
            
            // Obtener el valor del array multidimensional
            $value = $transaction;
            foreach (array_slice($keys, 1) as $key) {
                $value = $value[$key] ?? '';
            }
            $concatenatedValues .= $value;
        }

        // 🔥 Concatenar el timestamp y el secreto al final de los valores
        $concatenatedValues .= $timestamp . $eventsSecret;
        
        $expectedChecksum = hash('sha256', $concatenatedValues);

        return hash_equals($expectedChecksum, $signatureObj['checksum']);
    }
}