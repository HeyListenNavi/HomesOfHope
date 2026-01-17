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
            $table->foreignId('attended_by')->constrained('users');
            $table->string('status')->default('scheduled')->index(); //scheduled, completed, canceled, rescheduled
            $table->dateTime('scheduled_at'); 
            $table->dateTime('completed_at')->nullable();
            $table->string('location_type')->nullable(); // 'current_address', 'construction_site', 'office', 'other'
            $table->text('outcome_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};