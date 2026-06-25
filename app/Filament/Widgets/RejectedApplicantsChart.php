<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class RejectedApplicantsChart extends ChartWidget
{
    public ?string $filter = 'month';

    protected static ?string $heading = 'Rechazados por Staff vs IA';

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

    protected static ?int $sort = 9;

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $per = match ($this->filter) {
            'week' => 'perDay',
            'month' => 'perDay',
            'year' => 'perMonth',
        };

        $staffRejected = Trend::query(
            Applicant::where('process_status', 'staff_rejected')
        )
            ->dateColumn('created_at')
            ->between(start: $start, end: $end)
            ->{$per}()
            ->count();

        $aiRejected = Trend::query(
            Applicant::where('process_status', 'rejected')
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
                    'data' => $staffRejected->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#b91c1c',
                    'borderColor' => '#b91c1c',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'IA',
                    'data' => $aiRejected->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#f87171',
                    'borderColor' => '#f87171',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $staffRejected->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat($dateFormat)),
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
