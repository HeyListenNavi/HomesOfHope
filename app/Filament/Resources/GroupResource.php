<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use App\Services\GroupActions;
use Filament\Tables\Columns\TextColumn;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $modelLabel = 'Grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles del Grupo')
                    ->description('Configuración principal de la logística y capacidad.')
                    ->icon('heroicon-m-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Grupo')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-m-user-group'),

                        Forms\Components\DateTimePicker::make('date_time')
                            ->label('Fecha de Entrevista')
                            ->required()
                            ->seconds(false)
                            ->prefixIcon('heroicon-m-calendar-days')
                            ->native(false),

                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacidad Máxima')
                            ->required()
                            ->numeric()
                            ->default(25)
                            ->prefixIcon('heroicon-m-ticket')
                            ->minValue(fn(Get $get) => $get('current_members_count') ?? 0),

                        Forms\Components\TextInput::make('current_members_count')
                            ->label('Miembros Actuales')
                            ->numeric()
                            ->readOnly()
                            ->disabled() 
                            ->default(0)
                            ->prefixIcon('heroicon-m-users'),
                        
                        Forms\Components\TextInput::make('location')
                            ->label("Dirección Física")
                            ->placeholder('Calle, Número, Colonia...')
                            ->prefixIcon('heroicon-m-map-pin')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('location_link')
                            ->label('Enlace de Google Maps')
                            ->placeholder('https://maps.google.com/...')
                            ->prefixIcon('heroicon-m-link')
                            ->url() 
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Comunicación')
                    ->description('Mensaje de bienvenida e instrucciones para el grupo.')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make("message")
                            ->hiddenLabel()
                            ->placeholder('Escribe aquí las instrucciones que verán los aplicantes...')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre del Grupo')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user-group'),

                TextColumn::make('current_members_count')
                    ->label('Ocupación')
                    ->sortable()
                    ->formatStateUsing(fn($state, Group $record) => "{$state} / {$record->capacity}")
                    ->badge()
                    ->color(fn($state, Group $record) => match (true) {
                        $state >= $record->capacity => 'danger',
                        $state >= ($record->capacity * 0.8) => 'warning',
                        default => 'success',
                    })
                    ->icon(fn($state, Group $record) => $state >= $record->capacity ? 'heroicon-m-lock-closed' : 'heroicon-m-lock-open'),

                TextColumn::make('date_time')
                    ->label('Fecha de Entrevista')
                    ->dateTime('l d M, Y - h:i A')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->description(fn(Group $record) => ucfirst($record->date_time->locale('es')->diffForHumans())),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Creado')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Actualizado')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
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
            RelationManagers\ApplicantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
