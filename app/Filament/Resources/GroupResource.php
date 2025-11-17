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
                Forms\Components\Section::make('Datos del Grupo')
                    ->columns(2)
                    ->description('Completa la información principal del grupo y configura su capacidad y fecha de entrevista.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nombre del Grupo')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('date_time')
                            ->seconds(false)
                            ->required()
                            ->label('Fecha de Entrevista'),
                        Forms\Components\TextInput::make('capacity')
                            ->required()
                            ->numeric()
                            ->label('Capacidad Máxima')
                            ->default(25)
                            ->minValue(fn(Get $get) => $get('current_members_count') ?? 0),
                        Forms\Components\TextInput::make('current_members_count')
                            ->numeric()
                            ->label('Aplicantes en el Grupo')
                            ->disabled()
                            ->default(0),
                        Forms\Components\TextInput::make('location')
                            ->columnSpanFull()
                            ->label("Direccion")
                            ->default(0),
                        Forms\Components\TextInput::make('location_link')
                            ->columnSpanFull()
                            ->label('Link de la ubicacion')
                            ->default(0),

                    ]),
                Forms\Components\Section::make('Mensaje para los Aplicantes')
                    ->description('Redacta un mensaje personalizado que será mostrado a los miembros del grupo.')
                    ->schema([
                        Forms\Components\Textarea::make("message")
                            ->columnSpan(2)
                            ->hiddenLabel()
                            ->rows(10),
                    ]),
                Actions::make([
                    // Botón para reenviar la informacion a todos los aplicantes del grupo
                    Action::make('reSendGroupInfo')
                        ->label("Reenviar Informacion del grupo")
                        ->icon('heroicon-o-check-circle') 
                        ->requiresConfirmation()
                        ->modalHeading('Reenviar informacion del grupo')
                        ->modalDescription("¿Estás seguro reenviar la informacion a todos los aplicantes de este grupo? Esta acción no se puede deshacer.")
                        ->modalSubmitActionLabel('Sí, aprobar!')
                        ->disabled(true) // Desactivadas hasta utilizar templates
                        ->action(fn (Group $record) => GroupActions::reSendGroupMessage($record)),

                    // --- Botón de mensaje personalizado a todos los aplicantes del grupo
                    Action::make('sendCustomMessage')
                        ->label("Enviar mensaje personalizado")
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->form([
                            Forms\Components\Textarea::make('message')
                                ->label('Mensaje')
                                ->required()
                                ->rows(5)
                                ->placeholder('Escribe tu mensaje aquí...'),
                        ])
                        ->modalHeading('Enviar mensaje personalizado')
                        ->disabled(true) // Desactivadas hasta utilizar templates
                        ->action(function (array $data, Group $record) {
                            GroupActions::sendCustomMessageToGroup($record, $data['message']);
                        }),
                ])
                ->fullWidth()
                ->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->label('Capacidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_members_count')
                    ->numeric()
                    ->label('Aplicantes en el Grupo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->label('Fecha de Entrevista')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Creado en')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Actualizado en')
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
