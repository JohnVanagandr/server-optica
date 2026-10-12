<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Romper la relación en embed_widgets
        if (Schema::hasTable('embed_widgets') && Schema::hasColumn('embed_widgets', 'branch_id')) {
            Schema::table('embed_widgets', function (Blueprint $table) {
                $table->dropForeign('embed_widgets_branch_id_foreign');
                $table->dropColumn('branch_id');
            });
        }

        // 2. Romper la relación en users (NUEVA REGLA)
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_branch_id_foreign');
                $table->dropColumn('branch_id');
            });
        }

        // 3. Ahora sí podemos eliminar las tablas con seguridad
        Schema::dropIfExists('branch_product');
        Schema::dropIfExists('branches');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
