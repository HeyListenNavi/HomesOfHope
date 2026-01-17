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
        // Pre-calculamos para no hacer tantas consultas sueltas
        $counts = Applicant::query()
            ->selectRaw('process_status, count(*) as count')
            ->groupBy('process_status')
            ->pluck('count', 'process_status')
            ->toArray();

        // Helpers para sumar rápido (evita errores si el índice no existe)
        $get = fn($status) => $counts[$status] ?? 0;

        $approvedIA = $get('approved');
        $approvedStaff = $get('staff_approved');
        $rejectedIA = $get('rejected');
        $rejectedStaff = $get('staff_rejected');

        return [
            // 1. TOTAL GLOBAL
            Stat::make('Total de solicitantes', array_sum($counts))
                ->description('Total de aplicantes en base de datos')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Gráfica decorativa
                ->color('gray'),

            // 2. REQUIEREN ATENCIÓN (Bandeja de Entrada)
            Stat::make('Requiere Revisión', $get('requires_revision'))
                ->description('Solicitantes esperando ayuda manual')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'), // Naranja para llamar la atención

            // 3. APROBADOS (Suma IA + Staff)
            Stat::make('Aprobados', $approvedIA + $approvedStaff)
                ->description("IA: {$approvedIA} | Staff: {$approvedStaff}")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success') // Verde
                ->chart([2, 5, 8, 12, 20]),

            // 4. RECHAZADOS (Suma IA + Staff)
            Stat::make('Rechazados', $rejectedIA + $rejectedStaff)
                ->description("IA: {$rejectedIA} | Staff: {$rejectedStaff}")
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger') // Rojo
                ->chart([1, 1, 2, 0, 1]),
        ];
    }
}
