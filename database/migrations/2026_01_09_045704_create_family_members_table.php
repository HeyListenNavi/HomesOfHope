<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();            
            $table->foreignId('family_profile_id')->constrained('family_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->string('paternal_surname');
            $table->string('maternal_surname')->nullable();
            $table->date('birth_date');
            $table->string('curp')->unique()->nullable(); 
            $table->string('relationship'); // Ej: padre, madre, hijo, etc.
            $table->boolean('is_responsible')->default(false);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->text('medical_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};