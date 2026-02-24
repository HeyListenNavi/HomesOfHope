<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ApplicantsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $counts = Applicant::get()
            ->pluck('process_status')
            ->countBy()
            ->toArray();

        $get = fn($status) => $counts[$status] ?? 0;

        $approvedIA = $get('approved');
        $approvedStaff = $get('staff_approved');
        $rejectedIA = $get('rejected');
        $rejectedStaff = $get('staff_rejected');

        $start = now()->subDays(13);
        $end = now();

        $totalChart = Trend::model(Applicant::class)
            ->between(start: $start, end: $end)
            ->perDay()
            ->count()
            ->map(fn (TrendValue $value) => $value->aggregate)
            ->toArray();

        $approvedChart = Trend::query(Applicant::whereIn('process_status', ['approved', 'staff_approved']))
            ->dateColumn('updated_at')
            ->between(start: $start, end: $end)
            ->perDay()
            ->count()
            ->map(fn (TrendValue $value) => $value->aggregate)
            ->toArray();

        $rejectedChart = Trend::query(Applicant::whereIn('process_status', ['rejected', 'staff_rejected']))
            ->dateColumn('updated_at')
            ->between(start: $start, end: $end)
            ->perDay()
            ->count()
            ->map(fn (TrendValue $value) => $value->aggregate)
            ->toArray();

        return [
            Stat::make('Total de solicitantes', array_sum($counts))
                ->description('Total de aplicantes en base de datos')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart($totalChart)
                ->color('gray'),

            Stat::make('Requiere Revisión', $get('requires_revision'))
                ->description('Solicitantes esperando ayuda manual')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Aprobados', $approvedIA + $approvedStaff)
                ->description("IA: {$approvedIA} | Staff: {$approvedStaff}")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary')
                ->chart($approvedChart),

            Stat::make('Rechazados', $rejectedIA + $rejectedStaff)
                ->description("IA: {$rejectedIA} | Staff: {$rejectedStaff}")
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->chart($rejectedChart),
        ];
    }
}
