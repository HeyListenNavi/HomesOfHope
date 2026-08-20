<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Enums\FamilyStatus;
use App\Enums\HousingStatus;
use App\Enums\LandService;
use App\Enums\LandSize;
use App\Filament\Resources\FamilyProfileResource\Pages;
use App\Filament\Resources\FamilyProfileResource\RelationManagers;
use App\Models\FamilyProfile;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenLocationCode\OpenLocationCode;

class FamilyProfileResource extends Resource
{
    protected static ?string $model = FamilyProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Familias';

    protected static ?string $label = 'Perfil';

    protected static ?string $pluralLabel = 'Perfiles';

    protected static ?string $recordTitleAttribute = 'family_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['family_name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Estatus' => $record->status?->getLabel() ?? 'N/A',
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return FamilyProfileResource::getUrl('edit', ['record' => $record]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Forms\Components\FileUpload::make('family_photo_path')
                                    ->hiddenLabel()
                                    ->image()
                                    ->imagePreviewHeight('250')
                                    ->imageEditor()
                                    ->disk('r2')
                                    ->visibility('private')
                                    ->columnSpan(2)
                                    ->openable()
                                    ->extraAttributes(['class' => 'flex justify-center']),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('family_name')
                                            ->label('Nombre de la Familia')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Ej. Pérez López')
                                            ->extraInputAttributes(['style' => 'font-size: 1.5rem; font-weight: 800;']),

                                        Forms\Components\TextInput::make('slug')
                                            ->disabled()
                                            ->dehydrated(),

                                        Forms\Components\Placeholder::make('updated_at')
                                            ->label('Última actualización')
                                            ->content(fn ($record) => $record?->updated_at?->diffForHumans() ?? 'N/A')
                                            ->visible(fn ($record) => $record !== null),
                                    ])->columnSpan(4),
                            ])->columns(6),

                        Tabs::make('Información de la Familia')
                            ->tabs([
                                Tabs\Tab::make('Estatus y Gestión')
                                    ->icon('heroicon-s-adjustments-horizontal')
                                    ->schema([
                                        ToggleButtons::make('status')
                                            ->label('Estado Actual')
                                            ->options(FamilyStatus::class)
                                            ->inline()
                                            ->live()
                                            ->required()
                                            ->columnSpanFull(),

                                        Forms\Components\Toggle::make('construction_notified')
                                            ->label('Notificado sobre construcción')
                                            ->helperText('¿La familia ya fue notificada de que se construirá?')
                                            ->onIcon('heroicon-m-check')
                                            ->offIcon('heroicon-m-x-mark')
                                            ->visible(fn (Forms\Get $get) => $get('status') === FamilyStatus::Approved->value)
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('reason')
                                            ->label('Motivo / Razón')
                                            ->rows(3)
                                            ->autosize()
                                            ->live()
                                            ->visible(fn (Forms\Get $get) => in_array($get('status'), [FamilyStatus::NotEligible->value, FamilyStatus::DontBuild->value, FamilyStatus::Approved->value, FamilyStatus::Programmed->value]))
                                            ->columnSpanFull(),

                                        Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('interviewer_name')
                                                ->label('Entrevistador')
                                                ->prefixIcon('heroicon-s-user-circle'),

                                            Forms\Components\DatePicker::make('opened_at')
                                                ->label('Fecha de entrevista')
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->prefixIcon('heroicon-s-calendar'),
                                        ]),
                                    ]),

                                Tabs\Tab::make('Información del Terreno')
                                    ->icon('heroicon-m-map-pin')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('land_city')
                                                    ->label('Ciudad')
                                                    ->placeholder('Ej. Tijuana')
                                                    ->required(),

                                                Forms\Components\TextInput::make('land_colony')
                                                    ->label('Colonia')
                                                    ->placeholder('Ej. El Florido'),

                                                Forms\Components\TextInput::make('land_ownership_time')
                                                    ->label('Tiempo con el terreno')
                                                    ->hint('Ej. 2 años'),

                                                ToggleButtons::make('lives_on_land')
                                                    ->label('¿Vive en el terreno?')
                                                    ->options([
                                                        true => 'Sí Vive',
                                                        false => 'No Vive',
                                                    ])
                                                    ->colors([
                                                        true => 'success',
                                                        false => 'danger',
                                                    ])
                                                    ->icons([
                                                        true => 'heroicon-m-check-circle',
                                                        false => 'heroicon-m-x-circle',
                                                    ])
                                                    ->live()
                                                    ->required()
                                                    ->inline(),
                                            ])->columns(4),

                                        Forms\Components\Fieldset::make('Ubicación Geográfica')
                                            ->columns(3)
                                            ->schema([
                                                Forms\Components\Group::make()
                                                    ->columnSpan(1)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('land_address')
                                                            ->label('Dirección exacta / Referencias')
                                                            ->helperText('Indique lote, manzana o número exterior.'),

                                                        Forms\Components\TextInput::make('land_address_link')
                                                            ->label('Ubicación en Google Maps')
                                                            ->helperText('Pega un plus code para generar el enlace automáticamente y rellenar coordenadas')
                                                            ->prefixIcon('heroicon-m-map')
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Set $set, $state, $livewire) {
                                                                if (! str_starts_with($state, 'http') && str_contains($state, '+')) {
                                                                    $code = trim($state);

                                                                    if (OpenLocationCode::isValid($code)) {
                                                                        if (OpenLocationCode::isShort($code)) {
                                                                            $fullCode = OpenLocationCode::recoverNearest($code, 32.5149, -117.0382);
                                                                            $area = OpenLocationCode::decode($fullCode);
                                                                        } else {
                                                                            $area = OpenLocationCode::decode($code);
                                                                        }

                                                                        $set('land_latitude', round($area->latitudeCenter, 8));
                                                                        $set('land_longitude', round($area->longitudeCenter, 8));
                                                                        $set('land_map', ['lat' => $area->latitudeCenter, 'lng' => $area->longitudeCenter]);
                                                                    }

                                                                    $set('land_address_link', 'https://www.google.com/maps/search/?api=1&query='.urlencode($state));
                                                                    $livewire->dispatch('refreshMap');
                                                                }
                                                            })
                                                            ->suffixAction(
                                                                Forms\Components\Actions\Action::make('open_map')
                                                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                                                    ->color('primary')
                                                                    ->url(fn ($get) => $get('land_address_link'))
                                                                    ->openUrlInNewTab()
                                                                    ->disabled(fn ($get) => ! $get('land_address_link'))
                                                            ),

                                                        Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\TextInput::make('land_latitude')
                                                                    ->label('Latitud')
                                                                    ->numeric(),

                                                                Forms\Components\TextInput::make('land_longitude')
                                                                    ->label('Longitud')
                                                                    ->numeric(),
                                                            ]),
                                                    ]),

                                                Forms\Components\Group::make()
                                                    ->columnSpan(2)
                                                    ->schema([
                                                        Map::make('land_map')
                                                            ->hiddenLabel()
                                                            ->extraControl(['attributionControl' => false, 'liveLocation' => false])
                                                            ->markerHtml('<div class="marker-icon-container type-land">'.svg('heroicon-s-map')->toHtml().'</div>')
                                                            ->markerIconClassName('custom-div-icon')
                                                            ->markerIconSize([44, 44])
                                                            ->markerIconAnchor([22, 22])
                                                            ->afterStateHydrated(function (Set $set, $record) {
                                                                $set('land_map', [
                                                                    'lat' => $record->land_latitude ?? 32.5149,
                                                                    'lng' => $record->land_longitude ?? -117.0382,
                                                                ]);
                                                            })
                                                            ->afterStateUpdated(function (Set $set, $state, string $operation) {
                                                                if ($operation === 'view') {
                                                                    return;
                                                                }

                                                                $set('land_latitude', round($state['lat'], 8));
                                                                $set('land_longitude', round($state['lng'], 8));

                                                                $code = OpenLocationCode::encode($state['lat'], $state['lng']);
                                                                $set('land_address_link', 'https://www.google.com/maps/search/?api=1&query='.urlencode($code));
                                                            })
                                                            ->columnSpanFull()
                                                            ->tilesUrl('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png')
                                                            ->markerIconSize([36, 36])
                                                            ->showMyLocationButton(false)
                                                            ->liveLocation(false)
                                                            ->zoom(15)
                                                            ->clickable($form->getOperation() !== 'view')
                                                            ->draggable($form->getOperation() !== 'view')
                                                            ->showMarker(function () use ($form) {
                                                                $record = $form->getRecord();

                                                                return $record && ! empty($record->land_latitude) && ! empty($record->land_longitude);
                                                            })
                                                            ->extraStyles(['min-height: 400px', 'z-index: 0', 'border-radius: 16px'])
                                                            ->dehydrated(false),
                                                    ]),
                                            ]),

                                        Forms\Components\Fieldset::make('Pagos del Terreno')
                                            ->columns(3)
                                            ->columnSpanFull()
                                            ->schema([
                                                Forms\Components\Select::make('land_currency')
                                                    ->label('Moneda')
                                                    ->options(Currency::class)
                                                    ->default('mxn')
                                                    ->selectablePlaceholder(false)
                                                    ->native(false),

                                                Forms\Components\TextInput::make('land_total_cost')
                                                    ->label('Costo Total')
                                                    ->numeric()
                                                    ->prefix('$'),

                                                Forms\Components\TextInput::make('land_down_payment')
                                                    ->label('Cantidad del Enganche')
                                                    ->numeric()
                                                    ->prefix('$'),

                                                Forms\Components\TextInput::make('land_monthly_payment')
                                                    ->label('Mensualidad')
                                                    ->numeric()
                                                    ->prefix('$'),

                                                Forms\Components\DatePicker::make('land_last_payment_date')
                                                    ->label('Fecha Último Pago')
                                                    ->native(false),

                                                ToggleButtons::make('land_is_up_to_date')
                                                    ->label('¿Estatus de Pago?')
                                                    ->options([
                                                        true => 'Al corriente',
                                                        false => 'Con retraso',
                                                    ])
                                                    ->colors([
                                                        true => 'success',
                                                        false => 'danger',
                                                    ])
                                                    ->icons([
                                                        true => 'heroicon-m-check-circle',
                                                        false => 'heroicon-m-x-circle',
                                                    ])
                                                    ->inline(),
                                            ]),

                                        Forms\Components\Fieldset::make('Detalles del Terreno')
                                            ->columns(3)
                                            ->columnSpanFull()
                                            ->schema([
                                                Grid::make(1)
                                                    ->columnSpan(1)
                                                    ->schema([
                                                        Forms\Components\Toggle::make('land_is_flat')
                                                            ->label('¿Terreno plano?')
                                                            ->helperText('Marcar si el terreno no requiere nivelarse.')
                                                            ->onIcon('heroicon-m-check')
                                                            ->offIcon('heroicon-m-minus'),

                                                        Forms\Components\Select::make('land_size')
                                                            ->label('Medida del Terreno')
                                                            ->options(LandSize::class)
                                                            ->native(false)
                                                            ->placeholder('Selecciona la medida'),
                                                    ]),

                                                Forms\Components\CheckboxList::make('land_services')
                                                    ->label('Servicios Instalados')
                                                    ->options(LandService::class)
                                                    ->columns(2)
                                                    ->columnSpan(2)
                                                    ->gridDirection('row'),
                                            ]),
                                    ]),

                                Tabs\Tab::make('Casa Actual')
                                    ->icon('heroicon-m-home')
                                    ->schema([
                                        Grid::make(3)
                                            ->visible(fn (Forms\Get $get) => ! $get('lives_on_land'))
                                            ->schema([
                                                Forms\Components\TextInput::make('home_city')
                                                    ->label('Ciudad')
                                                    ->placeholder('Ej. Tijuana')
                                                    ->required(),

                                                Forms\Components\TextInput::make('home_colony')
                                                    ->label('Colonia')
                                                    ->placeholder('Ej. El Florido'),

                                                Forms\Components\TextInput::make('home_ownership_time')
                                                    ->label('Tiempo viviendo aquí')
                                                    ->hint('Ej. 2 años'),
                                            ])->columns(3),

                                        Forms\Components\Fieldset::make('Ubicación Geográfica')
                                            ->visible(fn (Forms\Get $get) => ! $get('lives_on_land'))
                                            ->columns(3)
                                            ->schema([
                                                Forms\Components\Group::make()
                                                    ->columnSpan(1)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('home_address')
                                                            ->label('Dirección exacta / Referencias')
                                                            ->helperText('Indique lote, manzana o número exterior.'),

                                                        Forms\Components\TextInput::make('home_address_link')
                                                            ->label('Ubicación en Google Maps')
                                                            ->helperText('Pega un plus code para generar el enlace automáticamente y rellenar coordenadas')
                                                            ->prefixIcon('heroicon-m-map')
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Set $set, $state, $livewire) {
                                                                if (! str_starts_with($state, 'http') && str_contains($state, '+')) {
                                                                    $code = trim($state);

                                                                    if (OpenLocationCode::isValid($code)) {
                                                                        if (OpenLocationCode::isShort($code)) {
                                                                            $fullCode = OpenLocationCode::recoverNearest($code, 32.5149, -117.0382);
                                                                            $area = OpenLocationCode::decode($fullCode);
                                                                        } else {
                                                                            $area = OpenLocationCode::decode($code);
                                                                        }

                                                                        $set('home_latitude', round($area->latitudeCenter, 8));
                                                                        $set('home_longitude', round($area->longitudeCenter, 8));
                                                                        $set('home_map', ['lat' => $area->latitudeCenter, 'lng' => $area->longitudeCenter]);
                                                                    }

                                                                    $set('home_address_link', 'https://www.google.com/maps/search/?api=1&query='.urlencode($state));
                                                                    $livewire->dispatch('refreshMap');
                                                                }
                                                            })
                                                            ->suffixAction(
                                                                Forms\Components\Actions\Action::make('open_map_home')
                                                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                                                    ->color('primary')
                                                                    ->url(fn ($get) => $get('home_address_link'))
                                                                    ->openUrlInNewTab()
                                                                    ->disabled(fn ($get) => ! $get('home_address_link'))
                                                            ),

                                                        Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\TextInput::make('home_latitude')
                                                                    ->label('Latitud')
                                                                    ->numeric(),

                                                                Forms\Components\TextInput::make('home_longitude')
                                                                    ->label('Longitud')
                                                                    ->numeric(),
                                                            ]),
                                                    ]),

                                                Forms\Components\Group::make()
                                                    ->columnSpan(2)
                                                    ->schema([
                                                        Map::make('home_map')
                                                            ->hiddenLabel()
                                                            ->extraControl(['attributionControl' => false, 'liveLocation' => false])
                                                            ->markerHtml('<div class="marker-icon-container type-home">'.svg('heroicon-s-home')->toHtml().'</div>')
                                                            ->markerIconClassName('custom-div-icon')
                                                            ->markerIconSize([44, 44])
                                                            ->markerIconAnchor([22, 22])
                                                            ->afterStateHydrated(function (Set $set, $record) {
                                                                $set('home_map', [
                                                                    'lat' => $record->home_latitude ?? 32.5149,
                                                                    'lng' => $record->home_longitude ?? -117.0382,
                                                                ]);
                                                            })
                                                            ->afterStateUpdated(function (Set $set, $state, string $operation) {
                                                                if ($operation === 'view') {
                                                                    return;
                                                                }

                                                                $set('home_latitude', round($state['lat'], 8));
                                                                $set('home_longitude', round($state['lng'], 8));

                                                                $code = OpenLocationCode::encode($state['lat'], $state['lng']);
                                                                $set('home_address_link', 'https://www.google.com/maps/search/?api=1&query='.urlencode($code));
                                                            })
                                                            ->columnSpanFull()
                                                            ->tilesUrl('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png')
                                                            ->markerIconSize([36, 36])
                                                            ->showMyLocationButton(false)
                                                            ->liveLocation(false)
                                                            ->zoom(15)
                                                            ->clickable($form->getOperation() !== 'view')
                                                            ->draggable($form->getOperation() !== 'view')
                                                            ->showMarker(function () use ($form) {
                                                                $record = $form->getRecord();

                                                                return $record && ! empty($record->home_latitude) && ! empty($record->home_longitude);
                                                            })
                                                            ->extraStyles(['min-height: 400px', 'z-index: 0', 'border-radius: 16px'])
                                                            ->dehydrated(false),
                                                    ]),
                                            ]),

                                        Forms\Components\Fieldset::make('Detalles de la casa')
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->schema([
                                                Forms\Components\Select::make('home_status')
                                                    ->label('Estatus de Vivienda')
                                                    ->options(HousingStatus::class)
                                                    ->native(false)
                                                    ->live(),

                                                Forms\Components\TextInput::make('home_owner_name')
                                                    ->label('Dueño de la casa')
                                                    ->placeholder('Nombre de quien renta/presta'),

                                                Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\Select::make('home_monthly_rent_currency')
                                                            ->label('Moneda')
                                                            ->visible(fn (Forms\Get $get) => $get('home_status') === HousingStatus::Rented->value)
                                                            ->options(Currency::class)
                                                            ->default('mxn')
                                                            ->native(false),

                                                        Forms\Components\TextInput::make('home_monthly_rent')
                                                            ->label('Monto de renta')
                                                            ->visible(fn (Forms\Get $get) => $get('home_status') === HousingStatus::Rented->value)
                                                            ->numeric()
                                                            ->prefix('$'),

                                                        ToggleButtons::make('home_has_receipts')
                                                            ->label('Comprobantes')
                                                            ->visible(fn (Forms\Get $get) => $get('home_status') === HousingStatus::Rented->value)
                                                            ->options([
                                                                true => 'Si Tiene',
                                                                false => 'No Tiene',
                                                            ])
                                                            ->colors([
                                                                true => 'success',
                                                                false => 'danger',
                                                            ])
                                                            ->icons([
                                                                true => 'heroicon-m-check-circle',
                                                                false => 'heroicon-m-x-circle',
                                                            ])
                                                            ->inline(),
                                                    ]),
                                            ])->visible(fn (Forms\Get $get) => ! $get('lives_on_land')),

                                        Forms\Components\Textarea::make('house_description')
                                            ->label('Descripción de la Casa Actual')
                                            ->placeholder('Describa materiales, distribución, condición, etc.')
                                            ->rows(5)
                                            ->autosize()
                                            ->columnSpanFull(),
                                    ]),

                                Tabs\Tab::make('Construcción')
                                    ->icon('heroicon-m-wrench-screwdriver')
                                    ->schema([
                                        Forms\Components\Fieldset::make('Fechas de Construcción')
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->schema([
                                                Forms\Components\DatePicker::make('building_start_date')
                                                    ->label('Fecha de Inicio')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y'),

                                                Forms\Components\DatePicker::make('building_finish_date')
                                                    ->label('Fecha de Finalización')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->afterOrEqual('building_start_date'),
                                            ]),

                                        Forms\Components\Fieldset::make('Equipo de Construcción')
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->schema([
                                                Forms\Components\TextInput::make('building_team')
                                                    ->label('Nombre del Equipo')
                                                    ->placeholder('Ej. Equipo Alpha')
                                                    ->prefixIcon('heroicon-m-users'),

                                                Forms\Components\TextInput::make('building_team_color')
                                                    ->label('Color del Equipo')
                                                    ->placeholder('Ej. Azul, Rojo, Verde')
                                                    ->prefixIcon('heroicon-m-swatch'),
                                            ]),
                                    ]),

                                Tabs\Tab::make('Notas y Comentarios')
                                    ->icon('heroicon-s-pencil-square')
                                    ->schema([
                                        Forms\Components\Textarea::make('general_observations')
                                            ->rows(5)
                                            ->autosize()
                                            ->label('Observaciones Generales')
                                            ->columnSpanFull(),

                                        Section::make('Adicciones')
                                            ->icon('heroicon-s-exclamation-triangle')
                                            ->compact()
                                            ->schema([
                                                Forms\Components\Checkbox::make('has_addictions')
                                                    ->label('Presencia de Adicciones')
                                                    ->live(),

                                                Forms\Components\Textarea::make('addictions_details')
                                                    ->label('Detalles de las Adicciones')
                                                    ->placeholder('Escribe aquí los detalles...')
                                                    ->autosize()
                                                    ->rows(2)
                                                    ->visible(fn (Forms\Get $get) => $get('has_addictions'))
                                                    ->required(fn (Forms\Get $get) => $get('has_addictions'))
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('family_photo_path')
                    ->label('Foto')
                    ->disk('r2')
                    ->visibility('private')
                    ->height(100)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('family_name')
                    ->label('Familia')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('family_name', 'like', "%{$search}%")
                            ->orWhereHas('members', function (Builder $query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('paternal_surname', 'like', "%{$search}%")
                                    ->orWhere('maternal_surname', 'like', "%{$search}%")
                                    ->orWhere('curp', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    })
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (FamilyProfile $record) => $record->home_address ? str($record->home_address)->limit(30) : 'Sin dirección registrada'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('responsibleMember.name')
                    ->label('Líder')
                    ->formatStateUsing(fn ($record) => $record->responsibleMember ? "{$record->responsibleMember->name}" : '-')
                    ->description(fn ($record) => $record->responsibleMember?->phone ?? '')
                    ->icon('heroicon-s-user')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('visits_count')
                    ->counts('visits')
                    ->label('Historial')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state.' Visita(s)')
                    ->color(fn ($state) => $state > 0 ? 'info' : 'danger')
                    ->icon(fn ($state) => $state > 0 ? 'heroicon-s-check-circle' : 'heroicon-s-exclamation-circle')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('lives_on_land')
                    ->label('¿Vive en el terreno?')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_address_link')
                    ->label('Link Terreno')
                    ->formatStateUsing(fn () => 'Ver Mapa')
                    ->icon('heroicon-m-map-pin')
                    ->color('primary')
                    ->url(fn ($record) => $record->land_address_link)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_colony')
                    ->label('Colonia (Terreno)')
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_ownership_time')
                    ->label('Tiempo con Terreno')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('land_is_flat')
                    ->label('Terreno Plano')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_size')
                    ->label('Medida')
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('building_team')
                    ->label('Equipo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn ($record) => $record->building_team_color),

                Tables\Columns\TextColumn::make('building_team_color')
                    ->label('Color')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color(fn ($record) => $record->building_team_color),

                Tables\Columns\TextColumn::make('construction_dates')
                    ->label('Fechas de Construcción')
                    ->getStateUsing(function (FamilyProfile $record): string {
                        $start = $record->building_start_date?->format('d/m/Y') ?? 'N/A';
                        $finish = $record->building_finish_date?->format('d/m/Y') ?? 'N/A';

                        return "{$start} - {$finish}";
                    })
                    ->sortable(['building_start_date', 'building_finish_date'])
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-s-eye')
                    ->color('gray'),

                Tables\Actions\Action::make('agendar_visita')
                    ->label('Agendar')
                    ->icon('heroicon-s-calendar-days')
                    ->color('primary')
                    ->button()
                    ->url(fn (FamilyProfile $record) => VisitResource::getUrl('create', ['family_profile_id' => $record->id])),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filtrar por Estado')
                    ->options(FamilyStatus::class),
                Tables\Filters\Filter::make('opened_at')
                    ->label('Fecha de entrevista')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->label('Mes')
                            ->options([
                                1 => 'Enero',
                                2 => 'Febrero',
                                3 => 'Marzo',
                                4 => 'Abril',
                                5 => 'Mayo',
                                6 => 'Junio',
                                7 => 'Julio',
                                8 => 'Agosto',
                                9 => 'Septiembre',
                                10 => 'Octubre',
                                11 => 'Noviembre',
                                12 => 'Diciembre',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('year')
                            ->label('Año')
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(date('Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['month'], fn (Builder $query, $month) => $query->whereMonth('opened_at', $month))
                            ->when($data['year'], fn (Builder $query, $year) => $query->whereYear('opened_at', $year));
                    }),
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
            RelationManagers\MembersRelationManager::class,
            RelationManagers\VisitsRelationManager::class,
            RelationManagers\TestimoniesRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\NotesRelationManager::class,
            RelationManagers\EvidencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilyProfiles::route('/'),
            'create' => Pages\CreateFamilyProfile::route('/create'),
            'view' => Pages\ViewFamilyProfile::route('/{record}'),
            'edit' => Pages\EditFamilyProfile::route('/{record}/edit'),
        ];
    }
}
