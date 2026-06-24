<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class RejectedPieChart extends ChartWidget
{
    protected static ?string $heading = 'Rechazados: Staff vs IA';

    protected static ?int $sort = 11;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $staffRejected = Applicant::where('process_status', 'staff_rejected')->count();
        $aiRejected = Applicant::where('process_status', 'rejected')->count();

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
