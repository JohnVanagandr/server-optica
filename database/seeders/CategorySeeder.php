<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categorias = ['Gafas de Sol', 'Gafas Formuladas', 'Lentes de Contacto', 'Gafas Deportivas', 'Accesorios'];
        
        foreach ($categorias as $categoria) {
            Category::create([
                'name' => $categoria,
                'description' => 'Catálogo de ' . strtolower($categoria)
            ]);
        }
    }
}