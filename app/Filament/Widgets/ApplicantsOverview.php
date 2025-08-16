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
                ->description('Número total en el sistema')
                ->descriptionIcon('heroicon-m-user-group'),

            Stat::make('En Proceso', Applicant::whereNull('is_approved')->count())
                ->description('Solicitantes en proceso')
                ->descriptionIcon('heroicon-m-clock'),

            Stat::make('Aprobados', Applicant::where('is_approved', true)->count())
                ->description('Solicitantes aceptados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('Rechazados', Applicant::where('is_approved', false)->count())
                ->description('Solicitantes rechazados')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
