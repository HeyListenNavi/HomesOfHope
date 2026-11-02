<?php

namespace App\Exports;

use App\Models\Group;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GroupApplicantsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected Group $group) {}

    public function collection()
    {
        return $this->group->attendances()->with('applicant')->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'Nombre',
            'Teléfono',
            'CURP',
            'Estatus',
            'Fecha Escaneo',
            'Fecha Registro',
        ];
    }

    public function map($attendance): array
    {
        $chatId = $attendance->applicant?->chat_id;
        $formattedPhone = $chatId ? (str_starts_with($chatId, '521') ? substr($chatId, 3) : $chatId) : '-';

        return [
            $attendance->attendance_code,
            $attendance->applicant?->applicant_name ?? 'Sin aplicante vinculado',
            $formattedPhone,
            $attendance->applicant?->curp ?? '-',
            $attendance->status?->getLabel() ?? 'Pendiente',
            $attendance->scanned_at ? $attendance->scanned_at->format('d/m/Y h:i A') : 'No escaneado',
            $attendance->created_at ? $attendance->created_at->format('d/m/Y h:i A') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
