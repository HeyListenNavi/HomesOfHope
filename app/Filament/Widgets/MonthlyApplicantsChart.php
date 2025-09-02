<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class ApplicantChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '240px';

    protected static ?string $heading = 'Distribución de tipos de solicitantes';

    protected function getData(): array
    {
        $statuses = [
            'Aprobados' => Applicant::where('process_status', 'approved')->count(),
            'En Proceso' => Applicant::where('process_status', 'in_progress')->count(),
            'Rechazados' => Applicant::where('process_status', 'rejected')->count(),
            'Requiere Revisión' => Applicant::where('process_status', 'requires_revision')->count(),
            'Cancelados' => Applicant::where('process_status', 'canceled')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Solicitantes',
                    'data' => array_values($statuses),
                    'backgroundColor' => [
                        '#61b346',
                        '#7fcf6a', 
                        '#4a8f36',
                        '#a3e08c',
                        '#356a1f', 
                    ],
                ],
            ],
            'labels' => array_keys($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
