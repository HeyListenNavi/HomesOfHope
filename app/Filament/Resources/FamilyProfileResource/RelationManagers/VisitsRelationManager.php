<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Visit;
use App\Filament\Resources\VisitResource;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    protected static ?string $title = 'Visitas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                /* ------------------------------------------------------------------
                 | Información general de la visita
                 |------------------------------------------------------------------ */
                Forms\Components\Section::make('Información de la visita')
                    ->schema([
                        Forms\Components\Grid::make(12)
                            ->schema([

                                Forms\Components\Select::make('status')
                                    ->label('Estado de la visita')
                                    ->options([
                                        'scheduled' => 'Programada',
                                        'completed' => 'Completada',
                                        'cancelled' => 'Cancelada',
                                    ])
                                    ->required()
                                    ->columnSpan(4),

                                Forms\Components\Select::make('location_type')
                                    ->label('Tipo de ubicación')
                                    ->options([
                                        'home' => 'Domicilio',
                                        'office' => 'Oficina',
                                        'remote' => 'Remota',
                                    ])
                                    ->required()
                                    ->columnSpan(4),

                                Forms\Components\Select::make('attended_by')
                                    ->label('Atendida por')
                                    ->relationship('attendant', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(4),
                            ]),
                    ]),

                /* ------------------------------------------------------------------
                 | Fechas
                 |------------------------------------------------------------------ */
                Forms\Components\Section::make('Fechas')
                    ->schema([
                        Forms\Components\Grid::make(12)
                            ->schema([

                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('Fecha programada')
                                    ->required()
                                    ->columnSpan(6),

                                Forms\Components\DateTimePicker::make('completed_at')
                                    ->label('Fecha de finalización')
                                    ->columnSpan(6),
                            ]),
                    ]),

                /* ------------------------------------------------------------------
                 | Resultado / Observaciones
                 |------------------------------------------------------------------ */
                Forms\Components\Section::make('Resultado y observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('outcome_summary')
                            ->label('Resumen del resultado')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('scheduled_at')
            ->columns([

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Programada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'scheduled' => 'Programada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('location_type')
                    ->label('Ubicación')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'home' => 'Domicilio',
                        'office' => 'Oficina',
                        'remote' => 'Remota',
                        default => $state,
                    })
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('attendant.name')
                    ->label('Atendida por')
                    ->searchable(),

                Tables\Columns\TextColumn::make('outcome_summary')
                    ->label('Resumen')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva visita'),
            ])
            ->actions([
                // 1. Botón de Edición Rápida (Modal/SlideOver)
                Tables\Actions\EditAction::make()
                    ->slideOver(), // Opcional: para que no ocupe toda la pantalla

                // 2. Botón para IR al Recurso (View/Edit Page)
                Tables\Actions\Action::make('open')
                    ->label('Gestionar') // Texto del botón
                    ->icon('heroicon-m-arrow-top-right-on-square') // Icono de "abrir externo"
                    ->color('gray') // Color neutro para no competir con acciones principales
                    // Aquí está la magia: Redirige a la página de edición del VisitResource
                    ->url(fn (Visit $record): string => VisitResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(), // Opcional: si quieres que abra otra pestaña
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
