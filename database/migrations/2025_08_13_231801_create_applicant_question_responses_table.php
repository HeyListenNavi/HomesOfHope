<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_question_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('question_text_snapshot');
            $table->text('user_response')->nullable();
            $table->enum('ai_decision', ['valid', 'not_valid', 'requires_supervision'])->nullable();            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_question_responses');
    }
};