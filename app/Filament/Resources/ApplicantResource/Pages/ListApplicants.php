<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListApplicants extends ListRecords
{
    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'aprobados' => Tab::make('Aprobados')
                ->icon('heroicon-o-cpu-chip')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'approved')),
            
            'aprobados_staff' => Tab::make('Aprobados por Staff')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'staff_approved')),

            'rechazados' => Tab::make('Rechazados')
                ->icon('heroicon-o-cpu-chip') 
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'rejected')),

            'rechazados_staff' => Tab::make('Rechazados por Staff')
                ->icon('heroicon-o-x-circle') 
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'staff_rejected')),

            'en_proceso' => Tab::make('En Progreso')
                ->icon('heroicon-o-arrow-path')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'in_progress')),

            'requiere_revision' => Tab::make('Requiere Revision')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'requires_revision')),
            
            'canceled' => Tab::make('Cancelado')
                ->icon('lucide-ban')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'canceled')),

            'todos' => Tab::make('Todos')
                ->icon('heroicon-o-list-bullet')
                ->modifyQueryUsing(fn (Builder $query) => $query),
        ];
    }
}