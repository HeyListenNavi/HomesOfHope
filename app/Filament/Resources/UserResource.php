<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;



class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Identificación')
                            ->description('Información básica de acceso.')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre Completo')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-user'),

                                TextInput::make('email')
                                    ->label('Correo electrónico')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->prefixIcon('heroicon-m-envelope'),

                                Select::make('roles')
                                    ->label('Asignar Roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull()
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => match ($record->name) {
                                        'admin' => '🛡️ Administrador',
                                        'connection' => '🔌 Conexión',
                                        'selection' => '🎯 Selección',
                                        'visit' => '🏠 Visita',
                                        'distribution' => '📦 Distribución',
                                        default => $record->name,
                                    })
                                    ->prefixIcon('heroicon-m-shield-check'),
                            ]),

                        Forms\Components\Section::make('Seguridad')
                            ->description('Control de credenciales.')
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('password')
                                    ->label('Nueva Contraseña')
                                    ->password()
                                    ->revealable()
                                    ->rule('confirmed')
                                    ->required(fn($context) => $context === 'create')
                                    ->dehydrated(fn($state) => filled($state))
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->helperText('Dejar en blanco para mantener la actual.')
                                    ->rule('min:8'),

                                TextInput::make('password_confirmation')
                                    ->label('Confirmar Contraseña')
                                    ->password()
                                    ->revealable()
                                    ->requiredWith('password')
                                    ->dehydrated(false),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->description(fn(User $record) => $record->email),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles asignados')
                    ->badge()
                    ->separator(',')
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'connection' => 'warning',
                        'selection' => 'info',
                        'distribution' => 'success',
                        'visit' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'admin' => 'Administrador',
                        'connection' => 'Conexión',
                        'selection' => 'Selección',
                        'visit' => 'Visita',
                        'distribution' => 'Distribución',
                        default => $state,
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Miembro desde')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Filtrar por Rol')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    // Evita que el admin se borre a sí mismo
                    ->hidden(fn(User $record) => $record->id === auth()->id()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('user.view_any') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('user.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('user.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('user.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('user.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('user.delete') ?? false;
    }
}
