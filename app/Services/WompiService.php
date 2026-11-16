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
    $this->publicKey = trim((string) config('services.wompi.public_key'));
    $this->integritySecret = trim((string) config('services.wompi.integrity_secret'));
  }

  public function generateReference(): string
  {
    return 'PAY-' . Str::random(10) . '-' . time();
  }

  public function generateIntegritySignature(string $reference, float $amount): string
  {
    // Forzar entero puro evita errores de precisión flotante en PHP
    $amountInCents = (int) round($amount * 100);

    $concatenatedString = $reference . $amountInCents . $this->currency . $this->integritySecret;

    return hash('sha256', $concatenatedString);
  }

  public function getCheckoutData(string $reference, float $amount): array
  {
    $amountInCents = (int) round($amount * 100);

    return [
      'public_key'      => $this->publicKey,
      'currency'        => $this->currency,
      'amount_in_cents' => $amountInCents,
      'reference'       => $reference,
      'signature'       => $this->generateIntegritySignature($reference, $amount),
      'environment'     => trim((string) config('services.wompi.env')),
    ];
  }
}
