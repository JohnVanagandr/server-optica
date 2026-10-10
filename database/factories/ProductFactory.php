<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

class ProductFactory extends Factory
{
public function definition(): array
    {
        $marcas = ['Ray-Ban', 'Oakley', 'Gucci', 'Prada', 'Hawkers', 'Genérica'];
        $estilos = ['Aviador', 'Wayfarer', 'Cat Eye', 'Lectura', 'Redondas'];

        return [
            // Le asignamos una de las 5 categorías al azar
            'category_id' => Category::inRandomOrder()->first()->id ?? 1, 
            
            'sku' => $this->faker->unique()->ean13(), 
            'name' => 'Gafas ' . $this->faker->randomElement($estilos) . ' ' . $this->faker->colorName(),
            'brand' => $this->faker->randomElement($marcas),
            'base_price' => $this->faker->randomFloat(2, 20, 300), 
            'ar_model_url' => 'https://tuoptica.com/modelos3d/' . $this->faker->uuid() . '.glb',
            'scale_x' => 1.0000,
            'scale_y' => 1.0000,
        ];
    }
}