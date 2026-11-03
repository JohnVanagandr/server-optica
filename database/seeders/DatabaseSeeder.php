<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            // SuperAdminSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            // ProductSeeder::class,
            PlanSeeder::class,
        ]);
    }
}