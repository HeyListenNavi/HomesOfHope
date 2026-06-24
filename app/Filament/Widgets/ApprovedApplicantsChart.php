<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ApprovedApplicantsChart extends ChartWidget
{
    protected static ?string $heading = 'Aprobados por Staff vs IA';

    protected static ?int $sort = 8;

    protected function getData(): array
    {
        $start = now()->startOfYear();
        $end = now()->endOfYear();

        $staffApproved = Trend::query(
            Applicant::where('process_status', 'staff_approved')
        )
            ->dateColumn('created_at')
            ->between(start: $start, end: $end)
            ->perMonth()
            ->count();

        $aiApproved = Trend::query(
            Applicant::where('process_status', 'approved')
        )
            ->dateColumn('created_at')
            ->between(start: $start, end: $end)
            ->perMonth()
            ->count();

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
            'labels' => $staffApproved->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat('M')),
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
