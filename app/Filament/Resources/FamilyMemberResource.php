<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamilyMemberResource\Pages;
use App\Models\FamilyMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FamilyMemberResource extends Resource
{
    protected static ?string $model = FamilyMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Miembros Familiares';

    protected static ?string $modelLabel = 'Miembro';

    protected static ?string $navigationGroup = 'Familias';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // COLUMNA IZQUIERDA: Datos Personales (La parte densa)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Identificación Personal')
                            ->description('Datos generales del miembro.')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\Grid::make(3) // Dividimos en 3 columnas para nombre
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre(s)')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('paternal_surname')
                                            ->label('Apellido Paterno')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('maternal_surname')
                                            ->label('Apellido Materno')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Grid::make(2) // Dividimos en 2 para detalles
                                    ->schema([
                                        Forms\Components\DatePicker::make('birth_date')
                                            ->label('Fecha de Nacimiento')
                                            ->required()
                                            ->native(false)
                                            ->maxDate(now())
                                            ->prefixIcon('heroicon-o-cake'),

                                        Forms\Components\TextInput::make('curp')
                                            ->label('CURP')
                                            ->maxLength(18)
                                            ->visibleOn('create') // Opcional: validaciones extras
                                            ->prefixIcon('heroicon-o-finger-print')
                                            ->placeholder('CLAVE ÚNICA...'),
                                        
                                        Forms\Components\TextInput::make('occupation')
                                            ->label('Ocupación')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-briefcase')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Section::make('Información Médica')
                            ->description('Condiciones, alergias o notas de salud relevantes.')
                            ->icon('heroicon-o-heart')
                            ->collapsed() // Colapsado por defecto para no saturar si está vacío
                            ->schema([
                                Forms\Components\RichEditor::make('medical_notes')
                                    ->label('Notas Clínicas / Médicas')
                                    ->toolbarButtons(['bold', 'bulletList', 'orderedList'])
                                    ->placeholder('Escribe aquí alergias, enfermedades crónicas, etc.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // COLUMNA DERECHA: Vinculación y Contacto
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Vinculación Familiar')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Select::make('family_profile_id')
                                    ->relationship('familyProfile', 'family_name')
                                    ->label('Familia')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([ // Permite crear familia al vuelo si es necesario
                                        Forms\Components\TextInput::make('family_name')
                                            ->required(),
                                    ]),

                                Forms\Components\Select::make('relationship')
                                    ->label('Parentesco')
                                    ->options([
                                        'Jefe de Familia' => 'Jefe(a) de Familia',
                                        'Esposo' => 'Esposo(a) / Pareja',
                                        'Hijo' => 'Hijo(a)',
                                        'Abuelo' => 'Abuelo(a)',
                                        'Nieto' => 'Nieto(a)',
                                        'Otro' => 'Otro',
                                    ])
                                    ->required()
                                    ->native(false),

                                Forms\Components\Toggle::make('is_responsible')
                                    ->label('Es Responsable Principal')
                                    ->helperText('Marca si esta persona responde por la familia.')
                                    ->onIcon('heroicon-m-check-badge')
                                    ->offIcon('heroicon-m-user')
                                    ->onColor('success'),
                            ]),

                        Forms\Components\Section::make('Contacto')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->prefixIcon('heroicon-o-device-phone-mobile'),
                                
                                Forms\Components\TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    //->prefixIcon('heroicon-o-at'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna 1: Nombre Completo y Rol
                Tables\Columns\TextColumn::make('name')
                    ->label('Miembro')
                    ->formatStateUsing(fn (FamilyMember $record) => "{$record->name} {$record->paternal_surname}")
                    ->description(fn (FamilyMember $record) => $record->maternal_surname) // Muestra el materno abajo en gris
                    ->searchable(['name', 'paternal_surname', 'maternal_surname'])
                    ->sortable()
                    ->icon(fn ($record) => $record->is_responsible ? 'heroicon-o-star' : 'heroicon-o-user')
                    ->iconColor(fn ($record) => $record->is_responsible ? 'warning' : 'gray'),

                // Columna 2: Familia a la que pertenece
                Tables\Columns\TextColumn::make('familyProfile.family_name')
                    ->label('Familia')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                // Columna 3: Parentesco
                Tables\Columns\TextColumn::make('relationship')
                    ->label('Parentesco')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Jefe de Familia' => 'success',
                        'Hijo' => 'info',
                        default => 'gray',
                    }),

                // Columna 4: Edad (Calculada al vuelo)
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Edad')
                    ->date('d/m/Y') // Muestra fecha
                    ->description(fn (FamilyMember $record) => $record->birth_date ? $record->birth_date->age . ' años' : '-')
                    ->sortable(),

                // Columna 5: Contacto Rápido
                Tables\Columns\TextColumn::make('phone')
                    ->label('Contacto')
                    ->icon('heroicon-o-phone')
                    ->copyable() // Permite copiar el teléfono con un click
                    ->toggleable(),
            ])
            ->filters([
                // Filtro por Familia
                Tables\Filters\SelectFilter::make('family_profile_id')
                    ->relationship('familyProfile', 'family_name')
                    ->label('Por Familia')
                    ->searchable()
                    ->preload(),

                // Filtro solo responsables
                Tables\Filters\TernaryFilter::make('is_responsible')
                    ->label('Responsables de Hogar'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('paternal_surname', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            // Aquí agregaremos managers después
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilyMembers::route('/'),
            'create' => Pages\CreateFamilyMember::route('/create'),
            'edit' => Pages\EditFamilyMember::route('/{record}/edit'),
        ];
    }
}