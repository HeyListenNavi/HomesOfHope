<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class RejectedPieChart extends ChartWidget
{
    public ?string $filter = 'month';

    protected static ?string $heading = 'Rechazados: Staff vs IA';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Esta Semana',
            'month' => 'Este Mes',
            'year' => 'Este Año',
        ];
    }

    private function getPeriodDateRange(): array
    {
        $filter = $this->filter ?? 'month';

        return match ($filter) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    protected static ?int $sort = 11;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $staffRejected = Applicant::where('process_status', 'staff_rejected')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        $aiRejected = Applicant::where('process_status', 'rejected')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [$staffRejected, $aiRejected],
                    'backgroundColor' => ['#b91c1c', '#f87171'],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => ['Staff', 'IA'],
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
