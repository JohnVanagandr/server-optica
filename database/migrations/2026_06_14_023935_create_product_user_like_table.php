<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_user_like', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps(); // Útil para saber CUÁNDO le dieron like
            
            // Un usuario solo puede darle un like a un mismo producto
            $table->unique(['product_id', 'user_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_user_like');
    }
};