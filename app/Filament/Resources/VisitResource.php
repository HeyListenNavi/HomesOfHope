<?php

namespace App\Filament\Resources;

use App\Enums\VisitLocationType;
use App\Enums\VisitStatus;
use App\Filament\Forms\Components\DropdownDatePicker;
use App\Filament\Resources\VisitResource\Pages;
use App\Filament\Resources\VisitResource\RelationManagers;
use App\Models\FamilyProfile;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

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
                                            ->getOptionLabelFromRecordUsing(fn (FamilyProfile $record) => Str::title($record->family_name))
                                            ->label('Familia a visitar')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->default(fn () => request('family_profile_id'))
                                            ->required()
                                            ->prefixIcon('heroicon-s-users')
                                            ->suffixAction(
                                                Action::make('open_family_profile')
                                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                                    ->url(fn ($state): string => $state ? route('filament.admin.resources.family-profiles.edit', $state) : '#')
                                                    ->openUrlInNewTab()
                                                    ->hidden(fn ($state): bool => blank($state)),
                                            ),

                                        ToggleButtons::make('location_type')
                                            ->label('Lugar')
                                            ->options(VisitLocationType::class)
                                            ->inline()
                                            ->live()
                                            ->required(),
                                    ]),

                                Forms\Components\Section::make('Datos de la Familia')
                                    ->icon('heroicon-s-information-circle')
                                    ->schema(function (Forms\Get $get): array {
                                        $familyProfileId = $get('family_profile_id');
                                        $locationType = $get('location_type');

                                        if (! $familyProfileId || ! $locationType) {
                                            return [
                                                Forms\Components\Placeholder::make('select_hint')
                                                    ->label('')
                                                    ->content('Selecciona una familia y un lugar para ver los datos.'),
                                            ];
                                        }

                                        $profile = FamilyProfile::find($familyProfileId);

                                        if (! $profile) {
                                            return [
                                                Forms\Components\Placeholder::make('not_found')
                                                    ->label('')
                                                    ->content('Familia no encontrada.'),
                                            ];
                                        }

                                        $profile->load('responsibleMember');
                                        $contact = $profile->responsibleMember;

                                        return match ($locationType) {
                                            VisitLocationType::Land->value => [
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('land_city')
                                                            ->label('Ciudad')
                                                            ->content($profile->land_city ?? '-'),

                                                        Forms\Components\Placeholder::make('land_colony')
                                                            ->label('Colonia')
                                                            ->content($profile->land_colony ?? '-'),

                                                        Forms\Components\Placeholder::make('land_address')
                                                            ->label('Dirección Exacta')
                                                            ->content($profile->land_address ?? '-'),

                                                        Forms\Components\Placeholder::make('action_link')
                                                            ->label('Mapa')
                                                            ->content($profile->land_address_link
                                                                ? new HtmlString('<a href="'.$profile->land_address_link.'" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline">'.'Abrir en Mapa'.'</a>')
                                                                : '-'),
                                                    ]),
                                            ],
                                            VisitLocationType::Home->value => [
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('home_city')
                                                            ->label('Ciudad')
                                                            ->content($profile->home_city ?? '-'),

                                                        Forms\Components\Placeholder::make('home_colony')
                                                            ->label('Colonia')
                                                            ->content($profile->home_colony ?? '-'),

                                                        Forms\Components\Placeholder::make('home_address')
                                                            ->label('Dirección Exacta')
                                                            ->content($profile->home_address ?? '-'),

                                                        Forms\Components\Placeholder::make('action_link')
                                                            ->label('Mapa')
                                                            ->content($profile->home_address_link
                                                                ? new HtmlString('<a href="'.$profile->home_address_link.'" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline">'.'Abrir en Mapa'.'</a>')
                                                                : '-'),
                                                    ]),
                                            ],
                                            VisitLocationType::Virtual->value => [
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('contact_name')
                                                            ->label('Nombre del Aplicante')
                                                            ->content($contact
                                                                ? $contact->name.' '.$contact->paternal_surname.' '.$contact->maternal_surname
                                                                : '-'),
                                                    ]),
                                            ],
                                            default => [
                                                Forms\Components\Placeholder::make('not_found')
                                                    ->label('')
                                                    ->content('Familia no encontrada.'),
                                            ]
                                        };
                                    }),

                                Forms\Components\Section::make('Teléfonos de la Familia')
                                    ->icon('heroicon-s-phone')
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->schema(function (Forms\Get $get): array {
                                        $familyProfileId = $get('family_profile_id');

                                        if (! $familyProfileId) {
                                            return [];
                                        }

                                        $profile = FamilyProfile::find($familyProfileId);

                                        if (! $profile) {
                                            return [];
                                        }

                                        $membersWithPhone = $profile->members()
                                            ->whereNotNull('phone')
                                            ->where('phone', '!=', '')
                                            ->get();

                                        if ($membersWithPhone->isEmpty()) {
                                            return [
                                                Forms\Components\Placeholder::make('no_phones')
                                                    ->label('')
                                                    ->content('Sin teléfonos registrados.'),
                                            ];
                                        }

                                        return $membersWithPhone->map(function ($member) {
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $member->phone);
                                            $relationshipLabel = $member->relationship?->getLabel() ?? '';
                                            $whatsAppUrl = "https://wa.me/{$cleanPhone}";
                                            $linkHtml = '<a href="'.$whatsAppUrl.'" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline">'.$member->phone.'</a>';

                                            return Forms\Components\Placeholder::make('phone_'.$member->id)
                                                ->label($relationshipLabel.': '.$member->full_name)
                                                ->content(new HtmlString($linkHtml));
                                        })->toArray();
                                    }),

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
                                    ->disabled(fn (Forms\Get $get) => $get('status') !== VisitStatus::Scheduled->value),
                            ]),

                        // COLUMNA DERECHA (Barra Lateral: Agenda y Estatus)
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Section::make('Agenda')
                                    ->icon('heroicon-s-calendar')
                                    ->schema([
                                        DropdownDatePicker::make('scheduled_at')
                                            ->label('Fecha Programada')
                                            ->required()
                                            ->minYear(date('Y') - 1)
                                            ->maxYear(date('Y') + 5)
                                            ->default(now()->format('Y-m-d')),

                                        Forms\Components\Select::make('attendants')
                                            ->relationship('attendants', 'name')
                                            ->label('Equipo de Visita')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->prefixIcon('heroicon-s-user-circle'),

                                        Forms\Components\DateTimePicker::make('completed_at')
                                            ->label('Cierre')
                                            ->native(false)
                                            // Solo visible cuando ya se cerró la visita
                                            ->visible(fn (Forms\Get $get) => in_array($get('status'), [VisitStatus::Completed->value, VisitStatus::Cancelled->value])),
                                    ]),

                                Forms\Components\Section::make('Estado')
                                    ->schema([
                                        ToggleButtons::make('status')
                                            ->hiddenLabel()
                                            ->options(VisitStatus::class)
                                            ->default(VisitStatus::Scheduled),
                                    ]),
                            ]),
                    ]),
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
                    ->dateTime('d \d\e F \d\e\l Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location_type')
                    ->label('Lugar')
                    ->badge()
                    ->icon(fn (?VisitLocationType $state): ?string => $state?->getIcon())
                    ->color(fn (?VisitLocationType $state): ?string => $state?->getColor())
                    ->formatStateUsing(fn (?VisitLocationType $state): string => $state?->getLabel() ?? 'Sin especificar'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attendants.name')
                    ->label('Equipo de Visita')
                    ->icon('heroicon-s-user')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(VisitStatus::class),

                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date) => $query->whereDate('scheduled_at', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date) => $query->whereDate('scheduled_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-s-eye')
                    ->color('gray'),
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
            'view' => Pages\ViewVisit::route('/{record}'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
        ];
    }
}
