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
            $table->boolean('is_land_owner')->default(false);
            $table->string('phone')->nullable();
            $table->string('occupation')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('education_level')->nullable();
            $table->integer('education_grade')->nullable();
            $table->decimal('weekly_income', 10, 2)->nullable();
            $table->string('origin_state')->nullable();
            $table->string('origin_country')->nullable();
            $table->string('religion')->nullable();
            $table->boolean('speaks_indigenous_language')->default(false);
            $table->string('indigenous_language')->nullable();
            $table->boolean('is_pregnant')->default(false);
            $table->integer('pregnancy_months')->nullable();
            $table->text('medical_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
