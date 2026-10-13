<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            
            // Datos básicos del anuncio
            $table->string('title')->comment('Nombre interno para el administrador');
            $table->string('image_path')->comment('Ruta de la imagen de la valla');
            $table->string('redirect_url')->nullable()->comment('Link de redirección al hacer clic');
            $table->boolean('is_active')->default(true)->comment('Estado de la valla');

            // Relaciones para el modelo híbrido (Cascada)
            // Son nullable porque un anuncio puede ser global, de categoría o de producto
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};