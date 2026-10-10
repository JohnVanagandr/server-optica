<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // El plan maestro deja de ser obligatorio: la suscripción ahora
            // se resuelve con las columnas simples definidas abajo.
            $table->unsignedBigInteger('plan_id')->nullable()->change();

            $table->string('plan_type')->nullable()->after('plan_id');
            $table->string('subscription_status')->default('inactive')->after('plan_type');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['plan_type', 'subscription_status', 'subscription_ends_at']);
            $table->unsignedBigInteger('plan_id')->nullable(false)->change();
        });
    }
};
