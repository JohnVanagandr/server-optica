<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'empresa'  => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // La regla 'confirmed' busca automáticamente el campo 'password_confirmation'
            'password' => ['required', 'confirmed', Password::defaults()], 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Tu nombre completo es requerido.',
            'empresa.required'   => 'El nombre de la empresa es obligatorio.',
            'email.required'     => 'Necesitamos tu correo para crear la cuenta.',
            'email.email'        => 'El correo no tiene un formato válido.',
            'email.unique'       => 'Este correo electrónico ya se encuentra registrado.',
            'password.required'  => 'Debes establecer una contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden. Verifícalas e intenta de nuevo.',
        ];
    }
}