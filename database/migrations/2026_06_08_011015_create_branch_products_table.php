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
        Schema::create('branch_product', function (Blueprint $table) {
            $table->id(); 
            
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            $table->decimal('custom_price', 10, 2)->nullable(); // Permitimos null por si usan el precio base
            $table->boolean('is_active')->default(true);
            
            // Restricción única para evitar que una sucursal agregue el mismo producto dos veces
            $table->unique(['branch_id', 'product_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_products');
    }
};
