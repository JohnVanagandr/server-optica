<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $guardName = 'web';

        // 1. Creación de Roles mínimos requeridos
        $superAdmin  = Role::firstOrCreate(['name' => 'super-admin',  'guard_name' => $guardName]);
        $admin       = Role::firstOrCreate(['name' => 'admin',        'guard_name' => $guardName]);
        $client      = Role::firstOrCreate(['name' => 'client',       'guard_name' => $guardName]);

        // 2. Asignación de Permisos

        // --- SUPER ADMIN ---
        // Acceso absoluto a todo
        $superAdmin->givePermissionTo(Permission::all());

        // --- ADMIN ---
        $admin->syncPermissions([
            'dashboard.admin', // 🚀 NUEVO: Acceso al panel
            'categories.index', 'categories.show', 'categories.store', 'categories.update', 'categories.destroy',
            'products.index', 'products.show', 'products.store', 'products.update', 'products.destroy', 'products.toggle-status',
            'users.index', 'users.show', 'users.toggle-status',
            'plans.index', 'plans.show',
            'branches.index', 'branches.show',
            'likes.index',
            'payments.index', 'payments.show',
            'advertisements.index', 'advertisements.show', 'advertisements.store', 'advertisements.update', 'advertisements.destroy', 'advertisements.toggle-status'
        ]);

        // --- CLIENTE (Consumidor final con acceso a planes y suscripción) ---
        $client->syncPermissions([
            'products.like',
            'plans.index',
            'plans.show',
            'plans.subscribe'
        ]);
    }
}