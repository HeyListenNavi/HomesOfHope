<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class ApprovedPieChart extends ChartWidget
{
    protected static ?string $heading = 'Aprobados: Staff vs IA';

    protected static ?int $sort = 10;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $staffApproved = Applicant::where('process_status', 'staff_approved')->count();
        $aiApproved = Applicant::where('process_status', 'approved')->count();

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
