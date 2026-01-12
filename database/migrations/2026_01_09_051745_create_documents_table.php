<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->string('document_type')->index(); // Ej: 'ine', 'contract', 'proof_of_address'
            $table->string('original_name');
            $table->string('file_path'); 
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->comment('Size in bytes');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};