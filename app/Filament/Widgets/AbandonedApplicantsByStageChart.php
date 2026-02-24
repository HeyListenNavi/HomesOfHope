<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class AbandonedApplicantsByStageChart extends ChartWidget
{
    protected static ?string $heading = 'Abandonos por Etapa (Inactividad)';
    protected static ?int $sort = 7;
    protected static string $color = 'gray';

    protected function getData(): array
    {
        $applicants = Applicant::with('currentStage')
            ->where('process_status', 'canceled')
            ->get(['current_stage_id']);

        $counts = $applicants->countBy(fn ($applicant) => $applicant->currentStage?->name ?? 'Sin etapa asignada');

        $labels = $counts->keys()->map(function ($label) {
            return explode("\n", wordwrap($label, 20, "\n"));
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Abandonos',
                    'data' => $counts->values()->toArray(),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'autoSkip' => false,
                    ],
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'aspectRatio' => 0.9,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
