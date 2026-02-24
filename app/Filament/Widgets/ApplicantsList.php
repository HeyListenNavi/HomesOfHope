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
    protected static ?string $heading = 'Últimos Aplicantes Registrados';
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applicant::query()->latest()
            )
            ->columns([
                TextColumn::make('applicant_name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('chat_id')
                    ->label('Número de Telefono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';

                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn($state) => 'https://wa.me/' . $state)
                    ->openUrlInNewTab(),

                TextColumn::make('currentStage.name')
                    ->label('Etapa')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('process_status')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in_progress' => 'En Progreso',
                        'approved' => 'IA: Aprobado',
                        'rejected' => 'IA: Rechazado',
                        'staff_approved' => 'Staff: Aprobado',
                        'staff_rejected' => 'Staff: Rechazado',
                        'requires_revision' => 'Revisión',
                        'canceled' => 'Cancelado',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'approved' => 'success',
                        'staff_approved' => 'success',
                        'rejected' => 'danger',
                        'staff_rejected' => 'danger',
                        'requires_revision' => 'warning',
                        'canceled' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'in_progress' => 'heroicon-m-arrow-path',
                        'approved' => 'heroicon-m-sparkles',
                        'staff_approved' => 'heroicon-m-check-badge',
                        'rejected' => 'heroicon-m-x-circle',
                        'staff_rejected' => 'heroicon-m-no-symbol',
                        'requires_revision' => 'heroicon-m-exclamation-triangle',
                        'canceled' => 'heroicon-m-x-mark',
                        default => 'heroicon-m-minus',
                    })
                    ->sortable(),
            ])
            ->paginated([5]);
    }
}
