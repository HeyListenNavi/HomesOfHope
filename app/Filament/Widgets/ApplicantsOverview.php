<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApplicantsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total de solicitantes', Applicant::count())
                ->description('Número total de solicitantes registrados')
                ->descriptionIcon('heroicon-m-user-group'),

            Stat::make('En Proceso', Applicant::where('process_status', 'in_progress')->count())
                ->description('Solicitantes que están en proceso')
                ->descriptionIcon('heroicon-m-clock'),

            Stat::make('Aprobados', Applicant::where('process_status', 'approved')->count())
                ->description('Solicitantes que han sido aprobados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Rechazados', Applicant::where('process_status', 'rejected')->count())
                ->description('Solicitantes que han sido rechazados')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
