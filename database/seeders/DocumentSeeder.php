<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Aseguramos que haya un usuario para asignar la subida
        $user = User::first() ?? User::factory()->create();

        // 1. Adjuntar documentos a Perfiles Familiares (ej. Comprobante domicilio)
        $profiles = FamilyProfile::all();
        foreach ($profiles as $profile) {
            Document::factory()->count(2)->create([
                'documentable_id' => $profile->id,
                'documentable_type' => FamilyProfile::class,
                'uploaded_by' => $user->id,
                'document_type' => 'proof_of_address'
            ]);
        }

        // 2. Adjuntar documentos a Miembros (ej. INE, CURP)
        $members = FamilyMember::all();
        foreach ($members as $member) {
            Document::factory()->create([
                'documentable_id' => $member->id,
                'documentable_type' => FamilyMember::class,
                'uploaded_by' => $user->id,
                'document_type' => 'ine'
            ]);
        }
    }
}