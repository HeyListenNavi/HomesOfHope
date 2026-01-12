<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonies', function (Blueprint $table) {
            $table->id();

            // Relación con el Perfil Familiar
            $table->foreignId('family_profile_id')
                  ->constrained('family_profiles')
                  ->cascadeOnDelete();

            // Idioma del testimonio: 'es', 'en', etc.
            $table->string('language')->default('es');

            // Archivo de audio (puede ser null si solo fue escrito)
            $table->string('audio_path')->nullable();

            // Contenidos de texto
            $table->text('transcription')->nullable(); // Texto completo
            $table->text('summary')->nullable();       // Resumen ejecutivo

            // Metadatos de grabación
            $table->foreignId('recorded_by')
                  ->constrained('users'); // Staff que grabó
            
            $table->dateTime('recorded_at'); // Cuándo sucedió (puede ser diferente al created_at)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonies');
    }
};