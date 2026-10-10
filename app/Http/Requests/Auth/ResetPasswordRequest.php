<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'     => 'El token de seguridad es obligatorio.',
            'email.required'     => 'El correo electrónico es obligatorio.',
            'password.required'  => 'La nueva contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}