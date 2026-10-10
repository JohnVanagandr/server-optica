<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Eliminamos el campo anterior de texto plano
            if (Schema::hasColumn('accounts', 'plan_type')) {
                $table->dropColumn('plan_type');
            }
            if (Schema::hasColumn('accounts', 'subscription_status')) {
                $table->dropColumn('subscription_status');
            }            
            // Estado booleano para pagos recurrentes (true = activo, false = fallido/suspendido)
            $table->boolean('subscription_active')->default(false)->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('plan_type')->nullable();
            $table->string('subscription_status')->nullable();
        });
    }
};