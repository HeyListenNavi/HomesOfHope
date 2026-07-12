<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_profile_id')->constrained('family_profiles')->cascadeOnDelete();
            $table->string('status')->default('scheduled')->index();
            $table->dateTime('scheduled_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('location_type')->nullable();
            $table->text('outcome_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('visit_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['visit_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_user');
        Schema::dropIfExists('visits');
    }
};
