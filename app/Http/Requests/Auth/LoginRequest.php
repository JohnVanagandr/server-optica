<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta petición.
     */
    public function authorize(): bool
    {
        return true; // Es público, cualquiera puede intentar iniciar sesión
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Mensajes personalizados para los errores.
     * Estos son los que React capturará e inyectará en la interfaz.
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'Ingresa un formato de correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }
}