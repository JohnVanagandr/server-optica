<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregamos la columna justo después del email para mantener el orden lógico
            $table->boolean('is_active')->default(true)->after('email');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Si hacemos un rollback, esta línea elimina la columna
            $table->dropColumn('is_active');
        });
    }
};