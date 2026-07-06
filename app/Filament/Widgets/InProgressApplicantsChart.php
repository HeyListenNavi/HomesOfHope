<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDatePeriod;
use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class InProgressApplicantsChart extends ChartWidget
{
    use HasDatePeriod;

    protected static ?string $heading = 'Aplicantes En Progreso por Etapa';

    protected static string $view = 'filament.widgets.date-period-chart-widget';

    protected static ?int $sort = 6;

    protected static string $color = 'info';

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $applicants = Applicant::with('currentStage')
            ->where('process_status', 'in_progress')
            ->whereBetween('created_at', [$start, $end])
            ->get(['current_stage_id']);

        $counts = $applicants->countBy(fn ($applicant) => $applicant->currentStage?->name ?? 'Sin etapa asignada');

        $labels = $counts->keys()->map(function ($label) {
            return explode("\n", wordwrap($label, 20, "\n"));
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Aplicantes',
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
                    'stacked' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'ticks' => [
                        'autoSkip' => false,
                    ],
                ],
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
