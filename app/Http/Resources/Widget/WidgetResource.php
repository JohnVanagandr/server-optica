<?php

namespace App\Http\Resources\Widget;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $widgetUrl = url('/w/' . $this->widget_code);

        $embedCode = '<!-- Inicia Widget de Catálogo Virtual y Probador --> ' .
            '<div style="width: 100%; max-width: 1200px; margin: 0 auto;">' .
            '<iframe src="' . $widgetUrl . '" width="100%" height="950" frameborder="0" scrolling="no" ' .
            'style="border: none; border-radius: 16px; background: transparent; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);" ' .
            'allow="camera; microphone; autoplay; fullscreen" title="Catálogo Virtual de Monturas"></iframe>' .
            '</div> <!-- Termina Widget de Catálogo Virtual -->';

        return [
            'id' => $this->id,
            'widget_code' => $this->widget_code,
            'title' => $this->title,
            'welcome_message' => $this->welcome_message,
            'primary_color' => $this->primary_color,
            'background_color' => $this->background_color,
            'whatsapp_number' => $this->whatsapp_number,
            'url' => $widgetUrl,
            'embed_code' => $embedCode, // 🚀 Enviamos el código listo desde el backend
        ];
    }
}