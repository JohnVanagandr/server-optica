<?php

namespace App\Http\Controllers\Api\Front;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * Renderiza la vista Blade del Iframe que se incrusta en las páginas de los clientes
     */
    public function renderIframe($widgetCode)
    {
        $widget = Widget::with('user')->where('widget_code', $widgetCode)->firstOrFail();
        
        $products = $widget->user->catalogProducts()
                         ->with('category')
                         ->where('is_active', true)
                         ->get();

        // Laravel renderiza la vista y le inyecta los datos
        return view('widget.iframe', [
            'widget' => $widget,
            'products' => $products
        ]);
    }
}