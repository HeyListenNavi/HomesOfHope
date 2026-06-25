<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ApprovedApplicantsChart extends ChartWidget
{
    public ?string $filter = 'month';

    protected static ?string $heading = 'Aprobados por Staff vs IA';

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

    protected static ?int $sort = 8;

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $per = match ($this->filter) {
            'week' => 'perDay',
            'month' => 'perDay',
            'year' => 'perMonth',
        };

        $staffApproved = Trend::query(
            Applicant::where('process_status', 'staff_approved')
        )
            ->dateColumn('created_at')
            ->between(start: $start, end: $end)
            ->{$per}()
            ->count();

        $aiApproved = Trend::query(
            Applicant::where('process_status', 'approved')
        )
            ->dateColumn('created_at')
            ->between(start: $start, end: $end)
            ->{$per}()
            ->count();

        $dateFormat = match ($this->filter) {
            'week' => 'D',
            'month' => 'j',
            'year' => 'M',
        };

        return [
            'datasets' => [
                [
                    'label' => 'Staff',
                    'data' => $staffApproved->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#15803d',
                    'borderColor' => '#15803d',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'IA',
                    'data' => $aiApproved->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#4ade80',
                    'borderColor' => '#4ade80',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $staffApproved->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat($dateFormat)),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
