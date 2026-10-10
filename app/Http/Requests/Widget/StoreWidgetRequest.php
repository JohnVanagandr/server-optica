<?php

namespace App\Http\Requests\Widget;

use Illuminate\Foundation\Http\FormRequest;

class StoreWidgetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_ids'   => 'required|array',
            'product_ids.*' => 'exists:products,id',
            
            // Validación estricta del objeto JSON
            'settings'               => 'sometimes|array',
            'settings.primary_color' => 'nullable|string|max:20',
            'settings.title'         => 'nullable|string|max:100',
            'settings.show_prices'   => 'nullable|boolean',
        ];
    }
}