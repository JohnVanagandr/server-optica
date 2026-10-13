<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar la caché de permisos para evitar colisiones
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Matriz de Permisos Granulares
        $modules = [
            'plans'      => ['index', 'show', 'store', 'update', 'destroy', 'subscribe'],
            'users'      => ['index', 'show', 'toggle-status'],
            'branches'   => ['index', 'show', 'update'],
            'categories' => ['index', 'show', 'store', 'update', 'destroy'],
            'products'   => ['index', 'show', 'store', 'update', 'destroy', 'like', 'toggle-status'],
            'widgets'    => ['index', 'show', 'store', 'update', 'destroy', 'embed-code'],
            'account'    => ['onboarding', 'subscription'],
            'likes'      => ['index'],
            'payments'   => ['index', 'show'], 
        ];

        $guardName = 'web';

        // Generación e Inserción Dinámica
        foreach ($modules as $model => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$model}.{$action}",
                    'guard_name' => $guardName
                ]);
            }
        }
    }
}