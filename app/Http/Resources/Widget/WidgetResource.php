<?php

namespace App\Http\Resources\Widget;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'widget_code' => $this->widget_code,
            'title' => $this->title,
            'welcome_message' => $this->welcome_message,
            'primary_color' => $this->primary_color,
            'background_color' => $this->background_color,
            'whatsapp_number' => $this->whatsapp_number,
        ];
    }
}