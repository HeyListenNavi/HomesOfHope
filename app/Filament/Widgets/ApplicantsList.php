<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ApplicantsList extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applicant::query()
                    ->latest()
            )
            ->columns([
                TextColumn::make('chat_id')->label('Número de Teléfono')->searchable(),
                TextColumn::make('currentStage.name')->label('Etapa Actual'),
                Tables\Columns\SelectColumn::make('process_status')->options([
                    'in_progress' => 'En Progreso',
                    'approved' => 'Aprobado',
                    'rejected' => 'Rechazado',
                    "requires_revision" => "Requiere Revision",
                    "canceled" => "Cancelado",
                ])->label('Estado del Proceso'),
                IconColumn::make('process_status')
                    ->label('Estado')
                    ->icon(fn(string $state): string => match ($state) {
                        'in_progress' => 'heroicon-o-arrow-path',
                        'approved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        'requires_revision' => 'heroicon-o-exclamation-triangle',
                        "canceled" => "lucide-ban",
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'requires_revision' => 'warning',
                        "canceled" => "gray",
                    }),
            ])
            ->paginated([5]);
    }
}
