<?php

namespace App\Filament\Resources;

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
                                // FOTO
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
                                    ])->columnSpan(4),
                            ])->columns(6),

                        Tabs::make('Detalles del Expediente')
                            ->tabs([
                                // PESTAÑA 1: GESTIÓN
                                Tabs\Tab::make('Gestión y Estatus')
                                    ->icon('heroicon-s-adjustments-horizontal') // Sólido
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

                                        Grid::make(2)->schema([
                                            Forms\Components\Select::make('responsible_member_id')
                                                ->relationship('responsibleMember', 'name')
                                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} {$record->paternal_surname}")
                                                ->searchable()
                                                ->preload()
                                                ->label('Aplicante')
                                                ->prefixIcon('heroicon-s-user'), // Sólido

                                            Forms\Components\DatePicker::make('opened_at')
                                                ->label('Fecha de entrevista')
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->required()
                                                ->prefixIcon('heroicon-s-calendar'), // Sólido
                                        ]),
                                    ]),

                                // PESTAÑA 2: UBICACIONES
                                Tabs\Tab::make('Ubicaciones')
                                    ->icon('heroicon-s-map-pin') // Sólido
                                    ->schema([
                                        Forms\Components\TextInput::make('current_address')
                                            ->label('Dirección Actual')
                                            ->prefixIcon('heroicon-s-home') // Sólido
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('current_address_link')
                                            ->label('Enlace Google Maps (Actual)')
                                            ->prefix('https://')
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('openMap')
                                                    ->icon('heroicon-s-arrow-top-right-on-square') // Sólido
                                                    ->url(fn ($state) => $state, shouldOpenInNewTab: true)
                                                    ->visible(fn ($state) => filled($state))
                                            )
                                            ->columnSpanFull(),

                                        Section::make('Terreno / Construcción')
                                            ->compact()
                                            ->icon('heroicon-s-building-office-2') // Sólido
                                            ->schema([
                                                Forms\Components\TextInput::make('construction_address')
                                                    ->label('Dirección del Terreno'),
                                                Forms\Components\TextInput::make('construction_address_link')
                                                    ->label('Link Maps Terreno')
                                                    ->url(),
                                            ]),
                                    ]),

                                // PESTAÑA 3: NOTAS
                                Tabs\Tab::make('Notas')
                                    ->icon('heroicon-s-pencil-square') // Sólido
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
            ->defaultSort('opened_at', 'desc')
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
                    ->weight(FontWeight::Bold)
                    ->description(fn (FamilyProfile $record) => $record->current_address ? str($record->current_address)->limit(30) : 'Sin dirección registrada'),

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
                    ->description(fn ($record) => $record->responsibleMember?->phone ?? '') // Muestra el teléfono debajo del nombre
                    ->icon('heroicon-s-user'),

                // COLUMNA VISITAS MEJORADA
                Tables\Columns\TextColumn::make('visits_count')
                    ->counts('visits')
                    ->label('Historial')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state.' Visita(s)') // Formato más humano
                    ->color(fn ($state) => $state > 0 ? 'info' : 'danger') // Rojo si es 0 (necesita atención)
                    ->icon(fn ($state) => $state > 0 ? 'heroicon-s-check-circle' : 'heroicon-s-exclamation-circle')
                    ->sortable(),
            ])
            ->actions([
                // ACCIÓN PERSONALIZADA: Agendar Visita Rápida
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
