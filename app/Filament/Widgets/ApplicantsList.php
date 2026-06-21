<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ApplicantsList extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

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
                    ->searchable(),

                TextColumn::make('chat_id')
                    ->label('Número de Telefono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn ($state) => 'https://wa.me/'.$state)
                    ->openUrlInNewTab(),

                TextColumn::make('currentStage.name')
                    ->label('Etapa')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('process_status')
                    ->label('Estatus')
                    ->badge()
                    ->sortable(),
            ])
            ->paginated([5]);
    }
}
