<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDatePeriod;
use App\Models\Applicant;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ApprovedApplicantsChart extends ChartWidget
{
    use HasDatePeriod;

    protected static ?string $heading = 'Aprobados por Staff vs IA';

    public ?string $filter = 'month';

    protected static string $view = 'filament.widgets.date-period-chart-widget';

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
