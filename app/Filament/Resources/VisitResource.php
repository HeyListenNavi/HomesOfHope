<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Visit;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\VisitResource\Pages;
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
                Forms\Components\Grid::make(3)
                    ->schema([
                        // COLUMNA IZQUIERDA (Contenido Principal)
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 2])
                            ->schema([
                                Forms\Components\Section::make('Detalles de la Visita')
                                    ->icon('heroicon-s-clipboard-document-list')
                                    ->schema([
                                        Forms\Components\Select::make('family_profile_id')
                                            ->relationship('familyProfile', 'family_name')
                                            ->label('Familia a visitar')
                                            ->searchable()
                                            ->preload()
                                            ->default(fn() => request('family_profile_id')) // Mantiene la magia del botón "Agendar"
                                            ->required()
                                            ->prefixIcon('heroicon-s-users'),

                                        ToggleButtons::make('location_type')
                                            ->label('Modalidad')
                                            ->options([
                                                'home' => 'Domicilio',
                                                'office' => 'Oficina',
                                                'virtual' => 'Virtual',
                                                'field' => 'Campo',
                                            ])
                                            ->icons([
                                                'home' => 'heroicon-s-home',
                                                'office' => 'heroicon-s-building-office',
                                                'virtual' => 'heroicon-s-video-camera',
                                                'field' => 'heroicon-s-map',
                                            ])
                                            ->colors([
                                                'home' => 'success',   // Verde = Ideal
                                                'office' => 'info',    // Azul = Formal
                                                'virtual' => 'warning', // Naranja = Distancia
                                                'field' => 'gray',     // Gris = Otro
                                            ])
                                            ->inline()
                                            ->required(),
                                    ]),

                                Forms\Components\Section::make('Notas para quien Visita')
                                    ->description('Instrucciones o contexto para la persona que realizará la visita.')
                                    ->icon('heroicon-s-pencil-square')
                                    ->schema([
                                        Forms\Components\Textarea::make('outcome_summary')
                                            ->label('Instrucciones / Contexto')
                                            ->placeholder('Ej: Verificar si terminaron el techo, preguntar por la salud del abuelo...')
                                            ->rows(4)
                                            ->autosize()
                                            ->columnSpanFull(),
                                    ])
                                    ->disabled(fn(Forms\Get $get) => $get('status') !== 'scheduled'),
                            ]),

                        // COLUMNA DERECHA (Barra Lateral: Agenda y Estatus)
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Section::make('Agenda')
                                    ->icon('heroicon-s-calendar')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('scheduled_at')
                                            ->label('Fecha Programada')
                                            ->required()
                                            ->native(false)
                                            ->prefixIcon('heroicon-s-clock'),

                                        Forms\Components\Select::make('attended_by')
                                            ->relationship('attendant', 'name')
                                            ->label('Responsable')
                                            ->default(Auth::id())
                                            ->searchable()
                                            ->preload()
                                            ->prefixIcon('heroicon-s-user-circle'),

                                        Forms\Components\DateTimePicker::make('completed_at')
                                            ->label('Cierre')
                                            ->native(false)
                                            // Solo visible cuando ya se cerró la visita
                                            ->visible(fn(Forms\Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
                                    ]),

                                Forms\Components\Section::make('Estado')
                                    ->schema([
                                        ToggleButtons::make('status')
                                            ->default('scheduled')
                                            ->hiddenLabel()
                                            ->options([
                                                'scheduled' => 'Programada',
                                                'completed' => 'Completada',
                                                'cancelled' => 'Cancelada',
                                                'no_show' => 'No se presentó',
                                                'rescheduled' => 'Reprogramada',
                                            ])
                                            ->colors([
                                                'scheduled' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                'no_show' => 'warning',
                                                'rescheduled' => 'gray',
                                            ])
                                            ->icons([
                                                'scheduled' => 'heroicon-s-calendar',
                                                'completed' => 'heroicon-s-check-circle',
                                                'cancelled' => 'heroicon-s-x-circle',
                                                'no_show' => 'heroicon-s-eye-slash',
                                                'rescheduled' => 'heroicon-s-arrow-path',
                                            ])
                                            ->default('scheduled'),
                                    ]),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('familyProfile.family_name')
                    ->label('Familia')
                    ->searchable()
                    ->icon('heroicon-s-users')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Fecha')
                    ->dateTime('d M Y')
                    ->description(fn($record) => $record->scheduled_at->format('h:i A')) // Hora debajo
                    ->sortable(),

                Tables\Columns\IconColumn::make('location_type')
                    ->label('Tipo')
                    ->icon(fn(string $state): string => match ($state) {
                        'home' => 'heroicon-s-home',
                        'office' => 'heroicon-s-building-office',
                        'virtual' => 'heroicon-s-video-camera',
                        default => 'heroicon-s-map-pin',
                    })
                    ->tooltip(fn(string $state): string => match ($state) {
                        'home' => 'Domiciliaria',
                        'office' => 'Oficina',
                        'virtual' => 'Virtual',
                        default => 'Campo',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'home' => 'success',
                        'office' => 'info',
                        'virtual' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'scheduled' => 'Programada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'Ausente',
                        'rescheduled' => 'Reprogramada',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): ?string => match ($state) {
                        'scheduled' => 'heroicon-s-calendar',
                        'completed' => 'heroicon-s-check-circle',
                        'cancelled' => 'heroicon-s-x-circle',
                        'no_show' => 'heroicon-s-eye-slash',
                        default => null,
                    }),

                Tables\Columns\TextColumn::make('attendant.name')
                    ->label('Atiende')
                    ->icon('heroicon-s-user')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'desc')
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
                            ->when($data['from'], fn(Builder $query, $date) => $query->whereDate('scheduled_at', '>=', $date))
                            ->when($data['until'], fn(Builder $query, $date) => $query->whereDate('scheduled_at', '<=', $date));
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
