<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pending')->index(); //pending, in_progress, completed, cancelled
            $table->string('priority')->default('medium'); //low, medium, high
            $table->dateTime('due_date')->nullable(); // Fecha límite
            $table->dateTime('completed_at')->nullable(); // Cuándo se cerró
            $table->foreignId('assigned_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};