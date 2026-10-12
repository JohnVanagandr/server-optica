<?php

namespace App\Http\Controllers\Api\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WidgetController extends Controller
{
    /**
     * Muestra los datos públicos del widget de una óptica en formato JSON
     */
    public function show($widget) // $widget sería el ID o Slug del usuario/óptica
    {
        // Tu lógica pública para devolver los productos del catálogo de esta óptica
    }

    /**
     * Renderiza la vista Blade del Iframe que se incrusta en otras páginas
     */
    public function renderIframe($widget)
    {
        // Tu lógica para retornar la vista (return view('widget.iframe', [...]))
    }
}