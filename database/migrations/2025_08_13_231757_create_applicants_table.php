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
            $table->string("applicant_name")->nullable();
            $table->string('curp')->unique()->nullable()->index();
            $table->enum("gender",["man", "woman"])->nullable();
            $table->foreignId('current_stage_id')->nullable()->constrained('stages')->cascadeOnDelete();
            $table->foreignId('current_question_id')->nullable()->constrained('questions')->cascadeOnDelete();
            $table->enum('process_status', ['in_progress', 'approved', 'rejected', 'requires_revision', 'canceled'])->default('in_progress');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('group_id')->nullable()->constrained('groups')->cascadeOnDelete();
            $table->enum('confirmation_status', ['pending', 'confirmed', 'canceled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};