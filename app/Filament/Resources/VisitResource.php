<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitResource\Pages;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\VisitResource\RelationManagers;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Visitas';

    protected static ?string $modelLabel = 'Visita';

    protected static ?string $navigationGroup = 'Familias';

    protected static ?int $navigationSort = 2;

    // CORRECCIÓN: Agregado 'static' aquí
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // GRUPO 1: Logística de la Visita (Izquierda)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Información General')
                            ->description('Detalles principales de la cita.')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                // Selección de Familia
                                Forms\Components\Select::make('family_profile_id')
                                    ->relationship('familyProfile', 'family_name')
                                    ->label('Familia')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->prefixIcon('heroicon-o-users'),

                                // Tipo de Ubicación (con iconos visuales)
                                Forms\Components\Select::make('location_type')
                                    ->label('Tipo de Ubicación')
                                    ->options([
                                        'home' => 'Domiciliaria',
                                        'office' => 'En Oficina',
                                        'virtual' => 'Virtual / Llamada',
                                        'field' => 'Campo / Otro',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-building-office-2'),

                                // Asignación del personal
                                Forms\Components\Select::make('attended_by')
                                    ->relationship('attendant', 'name')
                                    ->label('Atendido por')
                                    ->default(Auth::id())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->prefixIcon('heroicon-o-user'),
                            ])->columns(2),

                        // Resultado de la visita (Editor grande)
                        Forms\Components\Section::make('Resultados y Observaciones')
                            ->description('Resumen de lo sucedido durante la visita.')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\RichEditor::make('outcome_summary')
                                    ->label('Resumen del Resultado')
                                    ->placeholder('Describa los hallazgos, acuerdos y conclusiones...')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'bulletList', 'orderedList', 'link',
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // GRUPO 2: Estado y Tiempos (Derecha)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Estado y Agenda')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                // Estado con colores semánticos
                                Forms\Components\Select::make('status')
                                    ->label('Estado Actual')
                                    ->options([
                                        'scheduled' => 'Programada',
                                        'completed' => 'Completada',
                                        'cancelled' => 'Cancelada',
                                        'no_show' => 'No se presentó',
                                        'rescheduled' => 'Reprogramada',
                                    ])
                                    ->default('scheduled')
                                    ->required()
                                    ->native(false)
                                    ->selectablePlaceholder(false),

                                // Fechas
                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('Fecha Programada')
                                    ->required()
                                    ->native(false)
                                    ->seconds(false),

                                Forms\Components\DateTimePicker::make('completed_at')
                                    ->label('Fecha de Cierre')
                                    ->native(false)
                                    ->seconds(false)
                                    ->placeholder('Pendiente de cierre')
                                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    // CORRECCIÓN: Agregado 'static' aquí
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('familyProfile.family_name')
                    ->label('Familia')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-users'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Programada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'Ausente',
                        'rescheduled' => 'Reprogramada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'warning',
                        'rescheduled' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): ?string => match ($state) {
                        'scheduled' => 'heroicon-o-clock',
                        'completed' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => null,
                    }),

                Tables\Columns\IconColumn::make('location_type')
                    ->label('Tipo')
                    ->icon(fn (string $state): string => match ($state) {
                        'home' => 'heroicon-o-home',
                        'office' => 'heroicon-o-building-office',
                        'virtual' => 'heroicon-o-computer-desktop',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'home' => 'Domiciliaria',
                        'office' => 'Oficina',
                        'virtual' => 'Virtual',
                        default => 'Otro',
                    }),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Programada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->description(fn (Visit $record) => $record->attendant ? 'Por: ' . $record->attendant->name : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'scheduled' => 'Programada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ]),
                
                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
            RelationManagers\NotesRelationManager::class,
            RelationManagers\EvidencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisits::route('/'),
            'create' => Pages\CreateVisit::route('/create'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
        ];
    }
}