<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_type' => fake()->randomElement(DocumentType::cases())->value,
            'original_name' => fake()->word().'.pdf',
            'file_path' => 'documents/dummy/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 50000),
            'uploaded_by' => User::factory(),
        ];
    }
}
