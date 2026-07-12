<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicantStatus;
use App\Filament\Widgets\Concerns\HasDatePeriod;
use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class StatusDistributionChart extends ChartWidget
{
    use HasDatePeriod;

    protected static ?string $heading = 'Distribución de Estatus';

    public ?string $filter = 'month';

    protected static string $view = 'filament.widgets.date-period-chart-widget';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    public static function canAccess(): bool
    {
        return auth()->user()->can('applicant.view_any');
    }

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $data = Applicant::whereBetween('created_at', [$start, $end])
            ->get()
            ->pluck('process_status')
            ->countBy()
            ->toArray();

        $statuses = [
            ApplicantStatus::StaffApproved->value => ['label' => 'Staff: Aprobado', 'color' => '#15803d'],
            ApplicantStatus::Approved->value => ['label' => 'IA: Aprobado',    'color' => '#4ade80'],
            ApplicantStatus::InProgress->value => ['label' => 'En Progreso',     'color' => '#3b82f6'],
            ApplicantStatus::RequiresRevision->value => ['label' => 'Revisión Manual', 'color' => '#f59e0b'],
            ApplicantStatus::Rejected->value => ['label' => 'IA: Rechazado',   'color' => '#f87171'],
            ApplicantStatus::StaffRejected->value => ['label' => 'Staff: Rechazado', 'color' => '#b91c1c'],
            ApplicantStatus::Canceled->value => ['label' => 'Cancelado',       'color' => '#9ca3af'],
        ];

        $labels = [];
        $counts = [];
        $colors = [];

        foreach ($statuses as $key => $config) {
            $count = $data[$key] ?? 0;

            $labels[] = $config['label'];
            $counts[] = $count;
            $colors[] = $config['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Solicitantes',
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
