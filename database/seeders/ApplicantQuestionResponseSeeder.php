<?php

namespace Database\Seeders;

use App\Models\ApplicantQuestionResponse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicantQuestionResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApplicantQuestionResponse::factory()->count(100)->create();
    }
}
