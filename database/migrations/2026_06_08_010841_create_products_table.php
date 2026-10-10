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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->decimal('base_price', 10, 2);
            
            // Campos multimedia y URLs añadidos
            $table->string('ar_model_url')->nullable();
            $table->string('web_tracking_url')->nullable();
            
            // Escalas AR
            $table->decimal('scale_x', 8, 4)->default(1.0000);
            $table->decimal('scale_y', 8, 4)->default(1.0000);
            
            // Estados añadidos
            $table->boolean('is_featured')->default(false);
            
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};