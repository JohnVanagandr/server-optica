<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendResetLinkRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Paso 1: Recibe el email y envía el enlace de recuperación
     */
    public function sendResetLinkEmail(SendResetLinkRequest $request): JsonResponse
    {
        try {
            // El broker de Laravel genera el token y envía el correo automáticamente
            $status = Password::broker()->sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return $this->success([], 'Hemos enviado un enlace de recuperación a tu correo electrónico.');
            }

            return $this->error('No pudimos encontrar un usuario con esa dirección de correo electrónico.', 404);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al procesar la solicitud de recuperación');
        }
    }

    /**
     * Paso 2: Recibe el token, valida y actualiza la contraseña
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $status = Password::broker()->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    // Función pura que se ejecuta solo si el token es válido
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->success([], 'Tu contraseña ha sido restablecida exitosamente.');
            }

            return $this->error('El token de recuperación es inválido o ha expirado.', 400);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al restablecer la contraseña');
        }
    }
}