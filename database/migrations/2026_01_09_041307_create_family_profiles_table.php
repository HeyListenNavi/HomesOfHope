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
            $table->boolean('lives_on_land')->default(false);
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();

            // Ubicación de su Casa de Hoy (Home)
            $table->string('home_city')->nullable();
            $table->string('home_colony')->nullable();
            $table->string('home_address')->nullable();
            $table->string('home_address_link')->nullable();

            // Ubicación del Terreno de Construcción (Land)
            $table->string('land_city')->nullable();
            $table->string('land_colony')->nullable();
            $table->string('land_address')->nullable();
            $table->string('land_address_link')->nullable();

            // Información del Terreno (Land)
            $table->string('land_ownership_time')->nullable();
            $table->decimal('land_total_cost', 12, 2)->nullable();
            $table->decimal('land_down_payment', 12, 2)->nullable();
            $table->decimal('land_monthly_payment', 12, 2)->nullable();
            $table->string('land_currency')->default('mxn');
            $table->date('land_last_payment_date')->nullable();
            $table->boolean('land_is_up_to_date')->nullable()->default(false);
            $table->boolean('land_is_flat')->nullable()->default(false);
            $table->json('land_services')->nullable(); // Luz, Agua, Fosa, Drenaje, etc.

            // Situación Actual de la Vivienda (Home)
            $table->string('home_status')->nullable(); // rentada, prestada, etc.
            $table->string('home_ownership_time')->nullable();
            $table->string('home_owner_name')->nullable();
            $table->decimal('home_monthly_rent', 12, 2)->nullable();
            $table->string('home_monthly_rent_currency')->default('mxn');
            $table->boolean('home_has_receipts')->nullable()->default(false);
            $table->text('house_description')->nullable();

            $table->unsignedBigInteger('responsible_member_id')->nullable()->index();
            $table->date('opened_at');
            $table->date('closed_at')->nullable();
            $table->boolean('has_addictions')->nullable()->default(false);
            $table->text('addictions_details')->nullable();
            $table->text('general_observations')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_profiles');
    }
};
