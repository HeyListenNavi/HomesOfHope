<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Visit;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\FamilyProfile;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\FamilyProfileResource\Pages;
use App\Filament\Resources\FamilyProfileResource\RelationManagers;

class FamilyProfileResource extends Resource
{
    protected static ?string $model = FamilyProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Familias';
    protected static ?string $label = 'Perfil Familiar';
    protected static ?string $pluralLabel = 'Perfiles Familiares';

public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                // FOTO
                                Forms\Components\FileUpload::make('family_photo_path')
                                    ->hiddenLabel()
                                    ->image()
                                    ->imagePreviewHeight('250')
                                    ->imageEditor()
                                    ->disk('public')
                                    ->directory('families')
                                    ->visibility('public')
                                    ->columnSpan(2)
                                    ->extraAttributes(['class' => 'flex justify-center']),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('family_name')
                                            ->label('Nombre de la Familia')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Ej. Familia Pérez López')
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
                                                'prospect' => 'Prospecto',
                                                'active' => 'Activo',
                                                'in_follow_up' => 'Seguimiento',
                                                'closed' => 'Cerrado',
                                            ])
                                            ->colors([
                                                'prospect' => 'gray',
                                                'active' => 'success',
                                                'in_follow_up' => 'warning',
                                                'closed' => 'danger',
                                            ])
                                            ->icons([
                                                'prospect' => 'heroicon-s-eye',
                                                'active' => 'heroicon-s-bolt',
                                                'in_follow_up' => 'heroicon-s-clock',
                                                'closed' => 'heroicon-s-lock-closed',
                                            ])
                                            ->inline()
                                            ->required()
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\Select::make('responsible_member_id')
                                                ->relationship('responsibleMember', 'name')
                                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} {$record->paternal_surname}")
                                                ->searchable()
                                                ->preload()
                                                ->label('Líder de Hogar')
                                                ->prefixIcon('heroicon-s-user'), // Sólido

                                            Forms\Components\DatePicker::make('opened_at')
                                                ->label('Fecha de Apertura')
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
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
                                                    ->url(fn($state) => $state, shouldOpenInNewTab: true)
                                                    ->visible(fn($state) => filled($state))
                                            )
                                            ->columnSpanFull(),

                                        Forms\Components\Section::make('Terreno / Construcción')
                                            ->collapsed()
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
                                    ]),
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('family_photo_path')
                    ->label('')
                    ->disk('public')
                    ->visibility('public')
                    ->height(100),

                Tables\Columns\TextColumn::make('family_name')
                    ->label('Familia')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn(FamilyProfile $record) => $record->current_address ? str($record->current_address)->limit(30) : 'Sin dirección registrada'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'prospect' => 'Prospecto',
                        'active' => 'Activo',
                        'in_follow_up' => 'Seguimiento',
                        'closed' => 'Cerrado',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'prospect' => 'gray',
                        'active' => 'success',
                        'in_follow_up' => 'warning',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'prospect' => 'heroicon-s-eye',
                        'active' => 'heroicon-s-bolt',
                        'in_follow_up' => 'heroicon-s-clock',
                        'closed' => 'heroicon-s-lock-closed',
                        default => 'heroicon-s-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('responsibleMember.name')
                    ->label('Líder')
                    ->formatStateUsing(fn($record) => $record->responsibleMember ? "{$record->responsibleMember->name}" : '-')
                    ->description(fn($record) => $record->responsibleMember?->phone ?? '') // Muestra el teléfono debajo del nombre
                    ->icon('heroicon-s-user'),

                // COLUMNA VISITAS MEJORADA
                Tables\Columns\TextColumn::make('visits_count')
                    ->counts('visits')
                    ->label('Historial')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state . ' Visita(s)') // Formato más humano
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
