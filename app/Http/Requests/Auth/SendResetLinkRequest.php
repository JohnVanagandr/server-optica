<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendResetLinkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'Debe proporcionar una dirección de correo válida.',
        ];
    }
}