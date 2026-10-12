<?php

namespace App\Traits;

trait HasImage
{
    /**
     * Accesor virtual: $model->image_url
     * Convierte el 'image_path' de la base de datos en una URL completa para React.
     */
    public function getImageUrlAttribute()
    {
        // Apuntamos directamente a tu columna real
        $path = $this->image_path;

        // Si el producto no tiene imagen, devolvemos null para que React muestre el ícono por defecto
        if (!$path) {
            return null;
        }

        // Si la base de datos ya tiene una URL completa (ej. de un seeder o S3), la usamos tal cual
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Si es una ruta relativa local, le agregamos el dominio base de tu backend
        return asset('storage/' . $path);
    }
}