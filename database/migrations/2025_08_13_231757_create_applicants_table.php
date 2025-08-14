<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique();
            $table->string('curp')->unique()->nullable();
            $table->foreignId('current_stage_id')->nullable()->constrained('stages')->cascadeOnDelete();
            $table->foreignId('current_question_id')->nullable()->constrained('questions')->cascadeOnDelete();
            $table->string('process_status')->default('in_progress'); // in_progress, completed, rejected
            $table->boolean('is_approved')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('group_id')->nullable()->constrained('groups')->cascadeOnDelete();
            $table->json('evaluation_data')->nullable();
            $table->enum('confirmation_status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};