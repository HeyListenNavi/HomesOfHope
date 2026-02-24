<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class StatusDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Estatus';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = Applicant::get()
            ->pluck('process_status')
            ->countBy()
            ->toArray();

        $statuses = [
            'staff_approved'    => ['label' => 'Staff: Aprobado', 'color' => '#15803d'],
            'approved'          => ['label' => 'IA: Aprobado',    'color' => '#4ade80'],
            'in_progress'       => ['label' => 'En Progreso',     'color' => '#3b82f6'],
            'requires_revision' => ['label' => 'Revisión Manual', 'color' => '#f59e0b'],
            'rejected'          => ['label' => 'IA: Rechazado',   'color' => '#f87171'],
            'staff_rejected'    => ['label' => 'Staff: Rechazado','color' => '#b91c1c'],
            'canceled'          => ['label' => 'Cancelado',       'color' => '#9ca3af'],
        ];

        $labels = [];
        $counts = [];
        $colors = [];

        foreach ($statuses as $key => $config) {
            $count = $data[$key] ?? 0;

            $labels[] = $config['label'];
            $counts[] = $count;
            $colors[] = $config['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Solicitantes',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false
                ],
                'y' => [
                    'display' => false
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
