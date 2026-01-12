<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_profiles', function (Blueprint $table) {
            $table->id();
            
            $table->string('family_name'); 
            $table->string('slug')->unique();
            $table->string('status')->default('active')->index(); // 'prospect', 'active', 'in_follow_up', 'closed'
            $table->string('family_photo_path')->nullable();
            $table->string('current_address')->nullable();
            $table->string('current_address_link')->nullable();
            $table->string('construction_address')->nullable();
            $table->string('construction_address_link')->nullable();
            $table->unsignedBigInteger('responsible_member_id')->nullable()->index();
            $table->date('opened_at');
            $table->date('closed_at')->nullable();
            $table->text('general_observations')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_profiles');
    }
};