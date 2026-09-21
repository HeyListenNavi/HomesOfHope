<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicantStatus;
use App\Enums\RejectionReason;
use App\Filament\Widgets\Concerns\HasDatePeriod;
use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class RejectionReasonsChart extends ChartWidget
{
    use HasDatePeriod;

    protected static ?string $heading = 'Distribución de Razones de Rechazo';

    public ?string $filter = 'month';

    protected static string $view = 'filament.widgets.date-period-chart-widget';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    public static function canAccess(): bool
    {
        return auth()->user()->can('applicant.view_any');
    }

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $data = Applicant::whereIn('process_status', [ApplicantStatus::Rejected->value, ApplicantStatus::StaffRejected->value])
            ->whereBetween('created_at', [$start, $end])
            ->pluck('rejection_reason')
            ->countBy()
            ->toArray();

        $reasons = collect(RejectionReason::cases());
        $knownKeys = $reasons->pluck('value')->all();

        $configs = $reasons
            ->mapWithKeys(fn (RejectionReason $reason) => [
                $reason->value => [
                    'label' => $reason->getLabel(),
                    'color' => $reason->getColor(),
                ],
            ])
            ->put('other', ['label' => 'Otros', 'color' => '#64748b'])
            ->all();

        $labels = [];
        $counts = [];
        $colors = [];

        foreach ($configs as $key => $config) {
            if ($key === 'other') {
                $count = collect($data)->except($knownKeys)->sum();
            } else {
                $count = $data[$key] ?? 0;
            }

            $labels[] = $config['label'];
            $counts[] = $count;
            $colors[] = $config['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Aplicantes Rechazados',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
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
