<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ApplicantsOverview extends BaseWidget
{
    public ?string $filter = 'month';

    protected static ?int $sort = 1;

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Esta Semana',
            'month' => 'Este Mes',
            'year' => 'Este Año',
        ];
    }

    private function getPeriodDateRange(): array
    {
        $filter = $this->filter ?? 'month';

        return match ($filter) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    protected static string $view = 'filament.widgets.applicants-overview';

    protected function getStats(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $counts = Applicant::whereBetween('created_at', [$start, $end])
            ->get()
            ->pluck('process_status')
            ->countBy()
            ->toArray();

        $get = fn ($status) => $counts[$status] ?? 0;

        $approvedIA = $get('approved');
        $approvedStaff = $get('staff_approved');
        $rejectedIA = $get('rejected');
        $rejectedStaff = $get('staff_rejected');

        $per = match ($this->filter) {
            'week' => 'perDay',
            'month' => 'perDay',
            'year' => 'perMonth',
        };

        $chartStart = match ($this->filter) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
        };

        $totalChart = Trend::model(Applicant::class)
            ->between(start: $chartStart, end: $end)
            ->{$per}()
            ->count()
            ->map(fn (TrendValue $value) => $value->aggregate)
            ->toArray();

        $approvedChart = Trend::query(Applicant::whereIn('process_status', ['approved', 'staff_approved']))
            ->dateColumn('updated_at')
            ->between(start: $chartStart, end: $end)
            ->{$per}()
            ->count()
            ->map(fn (TrendValue $value) => $value->aggregate)
            ->toArray();

        $rejectedChart = Trend::query(Applicant::whereIn('process_status', ['rejected', 'staff_rejected']))
            ->dateColumn('updated_at')
            ->between(start: $chartStart, end: $end)
            ->{$per}()
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
