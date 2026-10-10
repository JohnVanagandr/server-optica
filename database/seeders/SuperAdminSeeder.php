<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::create([
            'name' => 'Súper Administrador',
            'email' => 'admin@tuoptica.com', 
            'password' => Hash::make('password123'), 
            'branch_id' => null,
        ]);

        // Le asignamos el rol con todos los poderes
        $superAdmin->assignRole('super-admin');
    }
}