<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Identificador único para el script del frontend
            $table->string('widget_code')->unique(); 
            // Configuración visual
            $table->string('title')->nullable();
            $table->text('welcome_message')->nullable();
            $table->string('primary_color', 10)->default('#0f172a');
            $table->string('background_color', 10)->default('#ffffff');
            $table->string('whatsapp_number', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};