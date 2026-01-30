<?php

namespace Database\Seeders;

use App\Models\Colony;
use Illuminate\Database\Seeder;

class ColonySeeder extends Seeder
{
    public function run(): void
    {
        $colonies = [
            'Centro',
            'Roma Norte',
            'Roma Sur',
            'Condesa',
            'Doctores',
            'Juárez',
            'Del Valle',
            'Narvarte',
        ];

        foreach ($colonies as $colony) {
            Colony::firstOrCreate([
                "city" => fake()->randomElement(["Tijuana", "Rosarito"]),
                'name' => $colony,
            ]);
        }
    }
}
