<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class ApplicantChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    protected static ?string $heading = 'Distribución de Estatus';

    protected function getData(): array
    {
        $statuses = [
            'Aprobados' => Applicant::where('process_status', 'approved')->count(),
            'En Proceso' => Applicant::where('process_status', 'in_progress')->count(),
            'Rechazados' => Applicant::where('process_status', 'rejected')->count(),
            'Requiere Revisión' => Applicant::where('process_status', 'requires_revision')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Solicitantes',
                    'data' => array_values($statuses),
                    'backgroundColor' => [
                        '#22c55e',
                        '#9ca3af',
                        '#f59e0b',
                        '#ef4444',
                    ],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => [
                'Aprobados', 
                'En Proceso', 
                'Requiere Revisión', 
                'Rechazados', 
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom', // Moves legend below chart
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}