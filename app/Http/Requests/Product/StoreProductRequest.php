<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'base_price'    => 'required|numeric|min:0',
            'image'         => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', 
            'ar_model_url'  => 'nullable|url|max:500',
            'web_tracking_url' => 'nullable|url|max:500',
            'sku'           => 'nullable|string|max:100|unique:products,sku',
            'brand'         => 'nullable|string|max:255',
            'is_featured'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'category_id.exists'    => 'La categoría seleccionada no es válida.',
            'ar_model_url.url'      => 'El enlace del modelo 360 debe ser una URL válida.',
            'web_tracking_url.url'  => 'El enlace de web-tracking debe ser una URL válida.',
            'image.image'           => 'El archivo debe ser una imagen válida (jpeg, png, jpg, webp).',
            'is_featured.boolean'   => 'El campo destacado debe ser verdadero o falso.',
        ];
    }
}