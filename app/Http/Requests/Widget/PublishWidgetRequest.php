<?php

namespace App\Http\Requests\Widget;

use Illuminate\Foundation\Http\FormRequest;

class PublishWidgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'config' => ['sometimes', 'array'],
            'config.title' => ['nullable', 'string', 'max:255'],
            'config.welcomeMessage' => ['nullable', 'string'],
            'config.primaryColor' => ['nullable', 'string', 'max:10'],
            'config.backgroundColor' => ['nullable', 'string', 'max:10'],
            'config.whatsappNumber' => ['nullable', 'string', 'max:20'],
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'exists:products,id'],
            'isUpdate' => ['sometimes', 'boolean'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'products.required' => 'Debe enviar al menos un producto para publicar el widget.',
            'products.*.id.exists' => 'Uno de los productos seleccionados no es válido.',
        ];
    }
}