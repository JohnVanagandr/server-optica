<?php

namespace App\Services;

use Illuminate\Support\Str;

class WompiService
{
    protected string $publicKey;
    protected string $integritySecret;
    protected string $currency = 'COP';

    public function __construct()
    {
        $this->publicKey = config('services.wompi.public_key');
        $this->integritySecret = config('services.wompi.integrity_secret');
    }

    /**
     * Genera una referencia única para la base de datos y Wompi
     */
    public function generateReference(): string
    {
        // Ej: PAY-5f4a1b2c3d-1698765432
        return 'PAY-' . Str::random(10) . '-' . time();
    }

    /**
     * Wompi exige una firma de integridad SHA256 (Referencia + Monto en Centavos + Moneda + Secreto)
     */
    public function generateIntegritySignature(string $reference, float $amount): string
    {
        // Wompi procesa el dinero en CENTAVOS. Ej: $10.000 COP = 1000000 centavos
        $amountInCents = $amount * 100;
        
        // Cadena concatenada exacta que pide Wompi
        $concatenatedString = $reference . $amountInCents . $this->currency . $this->integritySecret;
        
        return hash('sha256', $concatenatedString);
    }

    /**
     * Retorna los datos limpios que el Frontend necesita para abrir el Widget de Wompi
     */
    public function getCheckoutData(string $reference, float $amount): array
    {
        return [
            'public_key' => $this->publicKey,
            'currency'   => $this->currency,
            'amount_in_cents' => $amount * 100,
            'reference'  => $reference,
            'signature'  => $this->generateIntegritySignature($reference, $amount),
            'environment'=> config('services.wompi.env'),
        ];
    }
}