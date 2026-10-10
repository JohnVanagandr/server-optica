<?php

namespace App\Services;

use App\Models\Widget;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WidgetService
{
    /**
     * Publica o actualiza la configuración y los productos del widget del usuario.
     */
    public function publishWidget(User $user, ?array $config, array $productsData): Widget
    {
        // 1. Validación de Límites del Plan (Regla de negocio)
        $maxProducts = $user->plan ? (int) $user->plan->max_products : 0;
        $productsToSave = collect($productsData)->pluck('id')->toArray();
        
        if ($maxProducts !== -1 && count($productsToSave) > $maxProducts) {
            throw new InvalidArgumentException(
                'Límite excedido. Tu plan permite un máximo de ' . $maxProducts . ' monturas.', 
                403
            ); 
        }

        // 2. Buscar o inicializar el Widget del usuario
        $widget = Widget::firstOrNew(['user_id' => $user->id]);

        if (!$widget->exists) {
            $widget->widget_code = 'wid_' . Str::random(12);
        }

        // 3. Actualizar la configuración estética SOLO si viene explícitamente (evita sobrescribir con vacíos)
        if (!empty($config)) {
            $widget->title = $config['title'] ?? ($widget->title ?? 'Catálogo de Monturas');
            $widget->welcome_message = $config['welcomeMessage'] ?? ($widget->welcome_message ?? '');
            $widget->primary_color = $config['primaryColor'] ?? ($widget->primary_color ?? '#0f172a');
            $widget->background_color = $config['backgroundColor'] ?? ($widget->background_color ?? '#ffffff');
            $widget->whatsapp_number = $config['whatsappNumber'] ?? ($widget->whatsapp_number ?? null);
        } elseif (!$widget->exists) {
            // Valores por defecto si es la primera vez y no manda config
            $widget->title = 'Catálogo de Monturas';
            $widget->welcome_message = '';
            $widget->primary_color = '#0f172a';
            $widget->background_color = '#ffffff';
        }
        
        $widget->save();

        // 4. Sincronizar la relación de productos (Tabla pivote)
        $user->catalogProducts()->sync($productsToSave);

        return $widget;
    }

    /**
     * Obtiene el widget y su catálogo asociado para el cliente.
     */
    public function getClientCatalogData(User $user): array
    {
        $products = $user->catalogProducts()->get();
        $widget = Widget::where('user_id', $user->id)->first();

        return [
            'widget' => $widget,
            'products' => $products,
        ];
    }
}