<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class RejectedApplicantsChart extends ChartWidget
{
    protected static ?string $heading = 'Rechazados por Staff vs IA';

    protected static ?int $sort = 9;

    protected function getData(): array
    {
        $start = now()->startOfYear();
        $end = now()->endOfYear();

        $staffRejected = Trend::query(
            Applicant::where('process_status', 'staff_rejected')
        )
            ->dateColumn('created_at')
            ->between(start: $start, end: $end)
            ->perMonth()
            ->count();

        $aiRejected = Trend::query(
            Applicant::where('process_status', 'rejected')
        )
            ->dateColumn('created_at')
            ->between(start: $start, end: $end)
            ->perMonth()
            ->count();

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
            'labels' => $staffRejected->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat('M')),
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
