<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.string'   => 'El nombre debe ser una cadena de texto válida.',
            'name.max'      => 'El nombre no puede superar los 255 caracteres.',
            'name.unique'   => 'Esta categoría ya se encuentra registrada.',
        ];
    }
}