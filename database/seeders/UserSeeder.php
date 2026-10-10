<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tu usuario Administrador principal
        $admin = User::firstOrCreate(
            ['email' => 'jfbeccera@gmail.com'],
            [
                'name'      => 'John Becerra',
                'password'  => Hash::make('password123'),
                'is_active' => true,
                'branch_id' => null, 
            ]
        );
        $admin->assignRole('admin');

        // 2. Usuario Super Admin de pruebas
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@opticasaas.com'],
            [
                'name'      => 'Super Administrador',
                'password'  => Hash::make('password123'),
                'is_active' => true,
                'branch_id' => null,
            ]
        );
        $superAdmin->assignRole('super-admin');

        // 3. Usuario Cliente final de pruebas (encargado de suscribirse a los planes)
        $client = User::firstOrCreate(
            ['email' => 'cliente@opticasaas.com'],
            [
                'name'      => 'Cliente Final',
                'password'  => Hash::make('password123'),
                'is_active' => true,
                'branch_id' => null,
            ]
        );
        $client->assignRole('client');
    }
}