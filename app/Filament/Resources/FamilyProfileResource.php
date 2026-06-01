<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Enums\HousingStatus;
use App\Filament\Resources\FamilyProfileResource\Pages;
use App\Filament\Resources\FamilyProfileResource\RelationManagers;
use App\Models\FamilyProfile;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FamilyProfileResource extends Resource
{
    protected static ?string $model = FamilyProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Familias';

    protected static ?string $label = 'Perfil';

    protected static ?string $pluralLabel = 'Perfiles';

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
                                            ->options([
                                                'new' => 'Nuevo',
                                                'approved' => 'Aprobado',
                                                'in_process' => 'En Espera',
                                                'not_eligible' => 'No Califica',
                                                'potential' => 'Potencial',
                                                'built' => 'Construido',
                                                'dont_build' => 'No Construir',
                                            ])
                                            ->colors([
                                                'new' => 'gray',
                                                'approved' => 'success',
                                                'in_process' => 'warning',
                                                'not_eligible' => 'danger',
                                                'potential' => 'info',
                                                'built' => 'primary',
                                                'dont_build' => 'danger',
                                            ])
                                            ->icons([
                                                'new' => 'heroicon-s-plus-circle',
                                                'approved' => 'heroicon-s-check-circle',
                                                'in_process' => 'heroicon-s-clock',
                                                'not_eligible' => 'heroicon-s-lock-closed',
                                                'potential' => 'heroicon-s-eye',
                                                'built' => 'heroicon-s-building-office-2',
                                                'dont_build' => 'heroicon-s-x-circle',
                                            ])
                                            ->inline()
                                            ->required()
                                            ->columnSpanFull(),

                                        Grid::make(3)->schema([
                                            Forms\Components\Select::make('responsible_member_id')
                                                ->relationship('responsibleMember', 'name')
                                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} {$record->paternal_surname}")
                                                ->searchable()
                                                ->preload()
                                                ->label('Aplicante')
                                                ->prefixIcon('heroicon-s-user'),

                                            Forms\Components\Select::make('interviewer_id')
                                                ->relationship('interviewer', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->label('Entrevistador')
                                                ->prefixIcon('heroicon-s-user-circle'),

                                            Forms\Components\DatePicker::make('opened_at')
                                                ->label('Fecha de entrevista')
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->required()
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

                                                Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('land_address')
                                                            ->label('Dirección exacta / Referencias')
                                                            ->helperText('Indique lote, manzana o número exterior.'),

                                                        Forms\Components\TextInput::make('land_address_link')
                                                            ->label('Ubicación en Google Maps')
                                                            ->url()
                                                            ->prefixIcon('heroicon-m-map')
                                                            ->suffixAction(
                                                                Forms\Components\Actions\Action::make('open_map')
                                                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                                                    ->color('primary')
                                                                    ->url(fn ($get) => $get('land_address_link'))
                                                                    ->openUrlInNewTab()
                                                                    ->disabled(fn ($get) => ! $get('land_address_link'))
                                                            ),

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
                                                            ->inline(),
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
                                                Forms\Components\Toggle::make('land_is_flat')
                                                    ->label('¿Terreno plano?')
                                                    ->helperText('Marcar si el terreno no requiere nivelarse.')
                                                    ->onIcon('heroicon-m-check')
                                                    ->offIcon('heroicon-m-minus'),

                                                Forms\Components\CheckboxList::make('land_services')
                                                    ->label('Servicios Instalados')
                                                    ->options([
                                                        'electricity' => 'Luz eléctrica',
                                                        'water' => 'Agua potable',
                                                        'septic_tank' => 'Fosa séptica',
                                                        'sewage' => 'Drenaje municipal',
                                                    ])
                                                    ->columns(2)
                                                    ->columnSpan(2)
                                                    ->gridDirection('row'),
                                            ]),
                                    ]),

                                Tabs\Tab::make('Casa Actual')
                                    ->icon('heroicon-m-home')
                                    ->visible(fn (Forms\Get $get) => ! $get('lives_on_land'))
                                    ->schema([
                                        Grid::make(3)
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

                                                Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('home_address')
                                                            ->label('Dirección exacta / Referencias')
                                                            ->helperText('Indique lote, manzana o número exterior.'),

                                                        Forms\Components\TextInput::make('home_address_link')
                                                            ->label('Ubicación en Google Maps')
                                                            ->url()
                                                            ->prefixIcon('heroicon-m-map')
                                                            ->suffixAction(
                                                                Forms\Components\Actions\Action::make('open_map_home')
                                                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                                                    ->color('primary')
                                                                    ->url(fn ($get) => $get('home_address_link'))
                                                                    ->openUrlInNewTab()
                                                                    ->disabled(fn ($get) => ! $get('home_address_link'))
                                                            ),
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
                                                    ->placeholder('Nombre de quien renta/presta')
                                                    ->visible(fn (Forms\Get $get) => in_array($get('home_status'), ['rented', 'borrowed'])),

                                                Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\Select::make('home_monthly_rent_currency')
                                                            ->label('Moneda')
                                                            ->visible(fn (Forms\Get $get) => $get('home_status') === 'rented')
                                                            ->options(Currency::class)
                                                            ->default('mxn')
                                                            ->native(false),

                                                        Forms\Components\TextInput::make('home_monthly_rent')
                                                            ->label('Monto de renta')
                                                            ->visible(fn (Forms\Get $get) => $get('home_status') === 'rented')
                                                            ->numeric()
                                                            ->prefix('$'),

                                                        ToggleButtons::make('home_has_receipts')
                                                            ->label('Comprobantes')
                                                            ->visible(fn (Forms\Get $get) => $get('home_status') === 'rented')
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
                                            ]),

                                        Forms\Components\Textarea::make('house_description')
                                            ->label('Descripción de la Casa Actual')
                                            ->placeholder('Describa materiales, distribución, condición, etc.')
                                            ->rows(5)
                                            ->autosize()
                                            ->columnSpanFull(),
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
                    ->label('')
                    ->disk('r2')
                    ->visibility('private')
                    ->height(100),

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

                Tables\Columns\IconColumn::make('has_addictions')
                    ->label('Adicc.')
                    ->boolean()
                    ->trueIcon('heroicon-s-exclamation-triangle')
                    ->falseIcon('heroicon-s-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(fn (FamilyProfile $record) => $record->has_addictions ? "Con adicciones: {$record->addictions_details}" : 'Sin adicciones reportadas'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Nuevo',
                        'approved' => 'Aprobado',
                        'in_process' => 'En Proceso',
                        'not_eligible' => 'No Califica',
                        'potential' => 'Potencial',
                        'built' => 'Construido',
                        'dont_build' => 'No Construir',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'approved' => 'success',
                        'in_process' => 'warning',
                        'not_eligible' => 'danger',
                        'potential' => 'info',
                        'built' => 'primary',
                        'dont_build' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'new' => 'heroicon-s-plus-circle',
                        'approved' => 'heroicon-s-check-circle',
                        'in_process' => 'heroicon-s-clock',
                        'not_eligible' => 'heroicon-s-lock-closed',
                        'potential' => 'heroicon-s-eye',
                        'built' => 'heroicon-s-building-office-2',
                        'dont_build' => 'heroicon-s-x-circle',
                        default => '',
                    }),

                Tables\Columns\TextColumn::make('responsibleMember.name')
                    ->label('Líder')
                    ->formatStateUsing(fn ($record) => $record->responsibleMember ? "{$record->responsibleMember->name}" : '-')
                    ->description(fn ($record) => $record->responsibleMember?->phone ?? '')
                    ->icon('heroicon-s-user'),

                Tables\Columns\TextColumn::make('visits_count')
                    ->counts('visits')
                    ->label('Historial')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state.' Visita(s)')
                    ->color(fn ($state) => $state > 0 ? 'info' : 'danger')
                    ->icon(fn ($state) => $state > 0 ? 'heroicon-s-check-circle' : 'heroicon-s-exclamation-circle')
                    ->sortable(),

                Tables\Columns\IconColumn::make('lives_on_land')
                    ->label('¿Vive en el terreno?')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('home_address_link')
                    ->label('Link Casa')
                    ->formatStateUsing(fn () => 'Ver Mapa')
                    ->icon('heroicon-m-map')
                    ->color('primary')
                    ->url(fn ($record) => $record->home_address_link)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_address_link')
                    ->label('Link Terreno')
                    ->formatStateUsing(fn () => 'Ver Mapa')
                    ->icon('heroicon-m-map-pin')
                    ->color('primary')
                    ->url(fn ($record) => $record->land_address_link)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('home_colony')
                    ->label('Colonia (Casa)')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_colony')
                    ->label('Colonia (Terreno)')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_ownership_time')
                    ->label('Tiempo con Terreno')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_total_cost')
                    ->label('Costo Terreno')
                    ->money(fn ($record) => $record->land_currency->value ?? 'mxn')
                    ->description(fn ($record) => strtoupper($record->land_currency->value ?? 'MXN'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('land_last_payment_date')
                    ->label('Último Pago')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('land_is_up_to_date')
                    ->label('Al Corriente')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('land_is_flat')
                    ->label('Terreno Plano')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('home_status')
                    ->label('Estatus Vivienda')
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('home_ownership_time')
                    ->label('Tiempo Viviendo')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('home_monthly_rent')
                    ->label('Renta')
                    ->money(fn ($record) => $record->home_monthly_rent_currency->value ?? 'mxn')
                    ->description(fn ($record) => strtoupper($record->home_monthly_rent_currency->value ?? 'MXN'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('opened_at')
                    ->label('Apertura')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('closed_at')
                    ->label('Cierre')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('owners')
                    ->label('Dueños Terreno')
                    ->getStateUsing(fn ($record) => $record->members->where('is_land_owner', true)->pluck('name')->join(', '))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
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
                    ->options([
                        'prospect' => 'Prospecto',
                        'active' => 'Activo',
                        'in_follow_up' => 'En seguimiento',
                        'closed' => 'Cerrado',
                    ]),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilyProfiles::route('/'),
            'create' => Pages\CreateFamilyProfile::route('/create'),
            'edit' => Pages\EditFamilyProfile::route('/{record}/edit'),
        ];
    }
}
