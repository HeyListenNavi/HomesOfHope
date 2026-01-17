<?php

namespace App\Exports;

use App\Models\Group;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GroupApplicantsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    // 1. Obtenemos los aplicantes del grupo
    public function collection()
    {
        return $this->group->applicants;
    }

    // 2. Definimos los encabezados del Excel
    public function headings(): array
    {
        return [
            'Nombre',
            'Teléfono',
            'Fecha Registro',
            'CURP',
        ];
    }

    // 3. Mapeamos los datos de cada aplicante a las columnas
    public function map($applicant): array
    {
        return [
            $applicant->applicant_name,
            $applicant->chat_id, // O el campo de teléfono que uses
            $applicant->created_at->format('d/m/Y'),
            $applicant->curp,
        ];
    }

    // 4. (Opcional) Estilo: Pone negritas en la primera fila
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
