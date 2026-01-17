<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\FamilyMember;
use Filament\Support\Enums\FontWeight;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Miembros';

    protected static ?string $icon = 'heroicon-s-user-group'; // Icono sólido para la pestaña

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        // SECCIÓN 1: IDENTIDAD
                        Forms\Components\Section::make('Identificación Personal')
                            ->icon('heroicon-s-identification')
                            ->schema([
                                // Fila de Nombres
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre(s)')
                                            ->required()
                                            ->placeholder('Ej. Juan Carlos')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('paternal_surname')
                                            ->label('Apellido Paterno')
                                            ->required()
                                            ->placeholder('Ej. Pérez')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('maternal_surname')
                                            ->label('Apellido Materno')
                                            ->required()
                                            ->placeholder('Ej. López')
                                            ->maxLength(255),
                                    ]),

                                // Fila de Detalles
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('birth_date')
                                            ->label('Fecha de Nacimiento')
                                            ->required()
                                            ->native(false)
                                            ->maxDate(now())
                                            ->prefixIcon('heroicon-s-cake'),

                                        Forms\Components\TextInput::make('curp')
                                            ->label('CURP')
                                            ->maxLength(18)
                                            ->prefixIcon('heroicon-s-finger-print')
                                            ->placeholder('CURP')
                                            ->required()
                                            ->formatStateUsing(fn(?string $state) => strtoupper($state)),

                                        Forms\Components\TextInput::make('occupation')
                                            ->label('Ocupación')
                                            ->columnSpanFull()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-s-briefcase'),
                                    ]),
                            ]),

                        Forms\Components\Section::make('Ficha Médica')
                            ->description('Condiciones, alergias o notas de salud importantes.')
                            ->icon('heroicon-s-heart')
                            ->collapsed()
                            ->schema([
                                Forms\Components\Textarea::make('medical_notes')
                                    ->label('')
                                    ->rows(5)
                                    ->autoSize()
                                    ->placeholder('Escribe aquí alergias, enfermedades crónicas, tipo de sangre, etc.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Vinculación')
                            ->icon('heroicon-s-users')
                            ->schema([
                                Forms\Components\Select::make('family_profile_id')
                                    ->relationship('familyProfile', 'family_name')
                                    ->label('Pertenece a la Familia')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->prefixIcon('heroicon-s-home'),

                                Forms\Components\Select::make('relationship')
                                    ->label('Rol Familiar')
                                    ->options([
                                        'padre' => '👨 Padre de Familia',
                                        'madre' => '👩 Madre de Familia',
                                        'hijo' => '👶 Hijo(a)',
                                        'abuelo' => '👴 Abuelo(a)',
                                        'nieto' => '🧸 Nieto(a)',
                                        'otro' => '👤 Otro',
                                    ])
                                    ->required()
                                    ->native(false),

                                Forms\Components\Toggle::make('is_responsible')
                                    ->label('Es el Aplicante')
                                    ->helperText('¿Es la persona que aplica?')
                                    ->onIcon('heroicon-s-check-badge')
                                    ->offIcon('heroicon-s-user')
                                    ->onColor('success'),
                            ]),

                        Forms\Components\Section::make('Contacto Directo')
                            ->icon('heroicon-s-phone')
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono (WhatsApp)')
                                    ->required()
                                    ->prefixIcon('heroicon-s-chat-bubble-left-right'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Correo (Opcional)')
                                    ->email()
                                    ->placeholder('correo@ejemplo.com'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->formatStateUsing(fn(FamilyMember $record) => "{$record->name} {$record->paternal_surname} {$record->maternal_surname}")
                    ->searchable(['name', 'paternal_surname', 'maternal_surname'])
                    ->sortable()
                    ->icon(fn($record) => $record->is_responsible ? 'heroicon-s-star' : null)
                    ->iconColor('warning'),

                Tables\Columns\TextColumn::make('familyProfile.family_name')
                    ->label('Familia')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-s-home')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('relationship')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'padre', 'madre' => 'primary',
                        'hijo', 'nieto' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Edad')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? $state->age . ' años' : '-')
                    ->description(fn(FamilyMember $record) => $record->birth_date ? $record->birth_date->format('d M Y') : null),

                // CAMBIO AQUÍ: Lógica de WhatsApp
                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->icon('heroicon-s-chat-bubble-left-right')
                    ->url(fn($state) => $state ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $state) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('family_profile_id')
                    ->relationship('familyProfile', 'family_name')
                    ->label('Filtrar por Familia')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_responsible')
                    ->label('Solo Responsables'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->slideOver(),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Miembro')
                    ->icon('heroicon-s-plus')
                    ->modalHeading('Registrar Miembro Familiar')
                    ->slideOver(),
            ])
            ->defaultSort('paternal_surname', 'asc');
    }
}
