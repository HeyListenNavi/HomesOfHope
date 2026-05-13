<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OptionsReferenceSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Guía de Valores';
    }

    public function collection(): Collection
    {
        return new Collection([
            ['Campo', 'Valores Permitidos (Usar el código)', 'Descripción'],
            ['status', 'new, approved, in_process, not_eligible, potential, built, dont_build', 'Nuevo, Aprobado, En Espera, No Califica, Potencial, Construido, No Construir'],
            ['land_currency', 'mxn, usd', 'Pesos Mexicanos, Dólares'],
            ['home_status', 'rented, borrowed, other', 'Rentada, Prestada, Otro'],
            ['home_roof_condition', 'good, fair, poor', 'Bueno, Regular, Malo'],
            ['home_floor_condition', 'good, fair, poor', 'Bueno, Regular, Malo'],
            ['home_walls_condition', 'good, fair, poor', 'Bueno, Regular, Malo'],
            ['home_bathroom_location', 'inside, outside', 'Adentro, Afuera'],
            ['Booleans (si/no)', '1, true, yes, si', 'Cualquiera de estos se toma como VERDADERO. Vacío o 0 es FALSO.'],
            ['Servicios', 'electricity, water, septic_tank, sewage', 'Luz, Agua, Fosa Séptica, Drenaje. Separar con comas si hay más de uno.'],
            ['Fechas', 'DD/MM/YYYY', 'Ejemplo: 12/05/2026'],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
