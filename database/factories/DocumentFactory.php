<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_type' => fake()->randomElement(['ine', 'curp', 'proof_of_address', 'contract']),
            'original_name' => fake()->word() . '.pdf',
            'file_path' => 'documents/dummy/' . fake()->uuid() . '.pdf', 
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 50000), 
            'uploaded_by' => User::factory(), 
        ];
    }
}