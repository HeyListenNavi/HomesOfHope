<?php

namespace Database\Seeders;

use App\Models\Evidence;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class EvidenceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        
        // Solo agregamos evidencia a visitas que ya pasaron ('completed')
        $completedVisits = Visit::where('status', 'completed')->get();

        foreach ($completedVisits as $visit) {
            Evidence::factory()->count(rand(1, 3))->create([
                'visit_id' => $visit->id,
                'taken_by' => $user->id,
            ]);
        }
    }
}