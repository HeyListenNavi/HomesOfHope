<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_profiles', function (Blueprint $table) {
            // Add interviewer and house description
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('house_description')->nullable();

            // Drop old housing material and distribution fields
            $table->dropColumn([
                'home_roof_material',
                'home_roof_condition',
                'home_floor_material',
                'home_floor_condition',
                'home_walls_material',
                'home_walls_condition',
                'home_bedrooms_count',
                'home_bedrooms_description',
                'home_bathroom_location',
                'home_bathroom_description',
                'home_furniture_owned',
                'home_furniture_description',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('family_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('interviewer_id');
            $table->dropColumn('house_description');

            $table->string('home_roof_material')->nullable();
            $table->string('home_roof_condition')->nullable();
            $table->string('home_floor_material')->nullable();
            $table->string('home_floor_condition')->nullable();
            $table->string('home_walls_material')->nullable();
            $table->string('home_walls_condition')->nullable();
            $table->integer('home_bedrooms_count')->nullable();
            $table->text('home_bedrooms_description')->nullable();
            $table->string('home_bathroom_location')->nullable();
            $table->text('home_bathroom_description')->nullable();
            $table->boolean('home_furniture_owned')->nullable()->default(false);
            $table->text('home_furniture_description')->nullable();
        });
    }
};
