<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Capturamos el ID del producto que viene en la URL. 
        // Asegúrate de que coincida con el parámetro de tu ruta (usualmente 'id' o 'product')
        $productId = $this->route('id') ?? $this->route('product');

        return [
            'name'             => ['required', 'string', 'max:255'],
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'base_price'       => ['required', 'numeric', 'min:0'],
            'brand'            => ['nullable', 'string', 'max:255'],
            
            // El SKU debe ser único, excluyendo el ID del producto actual
            'sku'              => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($productId)
            ],
            
            // Enlaces externos obligatorios
            'ar_model_url'     => ['required', 'url', 'max:500'],
            'web_tracking_url' => ['required', 'url', 'max:500'],
            
            // La imagen es opcional al editar, pero si se envía debe ser válida
            'image'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_featured'      => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            // Mensajes para el Nombre
            'name.required'             => 'El nombre del producto no puede quedar vacío.',
            'name.max'                  => 'El nombre no puede exceder los 255 caracteres.',
            
            // Mensajes para el SKU
            'sku.required'              => 'El código SKU no puede quedar vacío.',
            'sku.unique'                => 'Este código SKU ya está siendo utilizado por otro producto.',
            'sku.max'                   => 'El código SKU no puede exceder los 50 caracteres.',
            
            // Mensajes para el Precio
            'base_price.required'       => 'El precio no puede quedar vacío.',
            'base_price.numeric'        => 'El precio debe ser un valor numérico.',
            'base_price.min'            => 'El precio no puede ser un valor negativo.',
            
            // Mensajes para la Categoría
            'category_id.required'      => 'Debe seleccionar una categoría.',
            'category_id.exists'        => 'La categoría seleccionada no es válida o no existe en la base de datos.',
            
            // Mensajes para Enlaces Externos
            'ar_model_url.required'     => 'El enlace del modelo 360 (AR) es obligatorio.',
            'ar_model_url.url'          => 'El enlace del modelo 360 debe ser una URL válida.',
            'ar_model_url.max'          => 'El enlace no puede exceder los 500 caracteres.',
            'web_tracking_url.required' => 'El enlace de web tracking es obligatorio.',
            'web_tracking_url.url'      => 'El enlace de web tracking debe ser una URL válida.',
            'web_tracking_url.max'      => 'El enlace no puede exceder los 500 caracteres.',
            
            // Mensajes para la Imagen
            'image.image'               => 'El archivo subido debe ser una imagen válida.',
            'image.mimes'               => 'La imagen debe estar en formato: jpeg, png, jpg o webp.',
            'image.max'                 => 'La imagen es demasiado pesada. El tamaño máximo permitido es 2MB.',
            'is_featured.boolean'       => 'El campo destacado debe ser verdadero o falso.',
        ];
    }
}