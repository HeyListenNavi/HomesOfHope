<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class ApprovedPieChart extends ChartWidget
{
    public ?string $filter = 'month';

    protected static ?string $heading = 'Aprobados: Staff vs IA';

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

    protected static ?int $sort = 10;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $staffApproved = Applicant::where('process_status', 'staff_approved')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        $aiApproved = Applicant::where('process_status', 'approved')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [$staffApproved, $aiApproved],
                    'backgroundColor' => ['#15803d', '#4ade80'],
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
