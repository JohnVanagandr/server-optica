<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embed_widget_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('embed_widget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Restricción: Un producto no puede estar duplicado en un mismo widget
            $table->unique(['embed_widget_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embed_widget_product');
    }
};