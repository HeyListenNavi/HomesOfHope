<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('family_profiles', function (Blueprint $table) {
            $table->decimal('home_latitude', 10, 8)->nullable()->after('home_address_link');
            $table->decimal('home_longitude', 11, 8)->nullable()->after('home_latitude');
            $table->decimal('land_latitude', 10, 8)->nullable()->after('land_address_link');
            $table->decimal('land_longitude', 11, 8)->nullable()->after('land_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'home_latitude',
                'home_longitude',
                'land_latitude',
                'land_longitude',
            ]);
        });
    }
};
