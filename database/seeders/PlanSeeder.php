<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan Básico (Sin descripción ni WhatsApp)
        Plan::updateOrCreate(
            ['type' => 'basico'],
            [
                'name'         => 'Plan Básico',
                'price'        => 120.000,
                'max_products' => 15,
                'max_widgets'  => 1,
                'description'  => null,
                'whatsapp'     => null,
            ]
        );

        // Plan Premium (Con descripción y número de WhatsApp habilitados)
        Plan::updateOrCreate(
            ['type' => 'premium'],
            [
                'name'         => 'Plan Premium',
                'price'        => 180.000,
                'max_products' => 20,
                'max_widgets'  => 1,
                'description'  => 'Incluye soporte prioritario, asesoramiento personalizado y mayor límite de inventario.',
                'whatsapp'     => '+573000000000',
            ]
        );
    }
}