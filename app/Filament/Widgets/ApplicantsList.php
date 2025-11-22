<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ApplicantsList extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Últimos Aplicantes Registrados';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applicant::query()->latest()
            )
            ->columns([
                TextColumn::make('applicant_name')
                    ->label('Nombre')
                    ->icon('heroicon-m-user')
                    ->searchable(),

                TextColumn::make('chat_id')
                    ->label('Número de Telefono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        
                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn ($state) => 'https://wa.me/' . $state)
                    ->openUrlInNewTab(),

                TextColumn::make('currentStage.name')
                    ->label('Etapa Actual')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('process_status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_progress' => 'En Progreso',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'requires_revision' => 'Revisión',
                        'canceled' => 'Cancelado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'requires_revision' => 'warning',
                        'canceled' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'in_progress' => 'heroicon-m-arrow-path',
                        'approved' => 'heroicon-m-check-circle',
                        'rejected' => 'heroicon-m-x-circle',
                        'requires_revision' => 'heroicon-m-exclamation-triangle',
                        default => 'heroicon-m-minus',
                    }),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->since()
                    ->color('gray')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->alignEnd(),
            ])
            ->paginated([5]);
    }
}