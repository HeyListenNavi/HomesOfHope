<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicantStatus;
use App\Filament\Widgets\Concerns\HasDatePeriod;
use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class RejectedPieChart extends ChartWidget
{
    use HasDatePeriod;

    protected static ?string $heading = 'Rechazados: Staff vs IA';

    public ?string $filter = 'month';

    protected static string $view = 'filament.widgets.date-period-chart-widget';

    protected static ?int $sort = 11;

    protected static ?string $maxHeight = '300px';

    public static function canAccess(): bool
    {
        return auth()->user()->can('applicant.view_any');
    }

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $staffRejected = Applicant::where('process_status', ApplicantStatus::StaffRejected->value)
            ->whereBetween('created_at', [$start, $end])
            ->count();
        $aiRejected = Applicant::where('process_status', ApplicantStatus::Rejected->value)
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
