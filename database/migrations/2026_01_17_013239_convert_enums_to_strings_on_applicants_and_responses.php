<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // applicants
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('gender')->nullable()->change();
            $table->string('process_status')->default('in_progress')->change();
            $table->string('confirmation_status')->default('pending')->change();
        });

        // applicant_question_responses
        Schema::table('applicant_question_responses', function (Blueprint $table) {
            $table->string('ai_decision')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Revertimos a ENUM originales
        Schema::table('applicants', function (Blueprint $table) {
            $table->enum('gender', ['man', 'woman'])->nullable()->change();
            $table->enum('process_status', ['in_progress', 'approved', 'rejected', 'requires_revision', 'canceled'])->default('in_progress')->change();
            $table->enum('confirmation_status', ['pending', 'confirmed', 'canceled'])->default('pending')->change();
        });

        Schema::table('applicant_question_responses', function (Blueprint $table) {
            $table->enum('ai_decision', ['valid', 'not_valid', 'requires_supervision'])->nullable()->change();
        });
    }
};
