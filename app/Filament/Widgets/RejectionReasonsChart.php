<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class RejectionReasonsChart extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Razones de Rechazo';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = Applicant::whereIn('process_status', ['rejected', 'staff_rejected'])
            ->pluck('rejection_reason')
            ->countBy()
            ->toArray();

        $statuses = [
            'no_children'       => ['label' => 'No tiene hijos',            'color' => '#ef4444'],
            'contract_issues'   => ['label' => 'Problemas con contrato',    'color' => '#f97316'],
            'not_owner'         => ['label' => 'No es dueño',               'color' => '#f59e0b'],
            'lives_too_far'     => ['label' => 'Muy lejos',                 'color' => '#eab308'],
            'less_than_a_year'  => ['label' => 'Menos de 1 año',            'color' => '#84cc16'],
            'late_payments'     => ['label' => 'Pagos atrasados',           'color' => '#22c55e'],
            'out_of_coverage'   => ['label' => 'Colonia No Atendida',       'color' => '#10b981'],
            'other'             => ['label' => 'Otros',                     'color' => '#64748b'],
        ];

        $labels = [];
        $counts = [];
        $colors = [];

        foreach ($statuses as $key => $config) {
            if ($key === 'other') {
                $count = collect($data)->except('no_children', 'contract_issues', 'not_owner', 'lives_too_far', 'less_than_a_year', 'late_payments', 'out_of_coverage')->sum();
            } else {
                $count = $data[$key] ?? 0;
            }

            $labels[] = $config['label'];
            $counts[] = $count;
            $colors[] = $config['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Aplicantes Rechazados',
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
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
