<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Tables;
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
                Tables\Columns\TextColumn::make('chat_id')->label('Número de Teléfono'),
                Tables\Columns\TextColumn::make('currentStage.name')->label('Etapa Actual'),
                Tables\Columns\SelectColumn::make('process_status')->options([
                    'in_progress' => 'En Progreso',
                    'completed' => 'Completado',
                    'rejected' => 'Rechazado',
                    'approved' => 'Aprobado',
                ])->label('Estado del Proceso'),
                Tables\Columns\IconColumn::make('is_approved')->boolean()->label('Aprobado'),
            ])
            ->paginated([5]);
    }
}
