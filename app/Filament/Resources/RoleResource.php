<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $modelLabel = 'Rol';
    protected static ?string $pluralModelLabel = 'Roles';
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Definición del Rol')
                    ->columnSpanFull()
                    ->description('Establezca el nombre y los privilegios de acceso.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Rol')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Ej. Admin, Visitador, Conexion...')
                            ->prefixIcon('heroicon-m-key')
                            ->columnSpanFull(),

                        Forms\Components\Section::make('Privilegios de Acceso')
                            ->description('Seleccione qué acciones puede realizar este rol.')
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\CheckboxList::make('permissions')
                                    ->label('Permisos')
                                    ->relationship('permissions', 'name')
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->columns(2)
                                    ->gridDirection('row')
                                    ->getOptionLabelFromRecordUsing(
                                        fn($record) =>
                                        str($record->name)
                                            ->replace('.', ': ')
                                            ->replace('_', ' ')
                                            ->title()
                                    )
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Rol')
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
                    ->icon('heroicon-m-shield-check')
                    ->searchable(),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Usuarios')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permisos')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
