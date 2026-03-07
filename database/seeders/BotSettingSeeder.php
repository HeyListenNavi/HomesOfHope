<?php

namespace Database\Seeders;

use App\Models\BotSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BotSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['name' => 'ask_name', 'value' => '¡Perfecto! 🎉 Empezamos. ¿Me dices tu nombre?'],
            ['name' => 'ask_curp', 'value' => 'Pedimos tu CURP solo para confirmar tu identidad y evitar duplicar solicitudes. Esto nos ayuda a llevar un registro claro y ordenado de las familias que participan. Tu información está segura y se usa únicamente para este proceso, no se comparte con nadie más. Por favor, ¿me puedes escribir tu CURP?'],
            ['name' => 'ask_gender', 'value' => 'Basado en el CURP y Nombre que dio el aplicante infiere su genero'],
        ];

        foreach ($settings as $setting) {
            BotSetting::updateOrCreate(
                [
                    'name' => $setting['name'],
                    'type' => 'initial_questions',
                    'value' => $setting['value']
                ]
            );
        }
    }
}
