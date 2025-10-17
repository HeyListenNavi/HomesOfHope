<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource\RelationManagers\ConversationMessagesRelationManager;
use App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource\RelationManagers;
use App\Filament\Resources\ConversationResource\RelationManagers\ConversationMessagesRelationManager as RelationManagersConversationMessagesRelationManager;
use App\Models\Conversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $modelLabel = 'Conversación';

    protected static ?string $pluralModelLabel = 'Conversaciones';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Auditoría y Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Conversación')
                    ->description('Información principal de la conversación con el solicitante.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('chat_id')
                            ->required()
                            ->maxLength(255)
                            ->label('Número de Teléfono'),
                        Forms\Components\TextInput::make('user_name')
                            ->maxLength(255)
                            ->label('Nombre'),
                    ]),
                Forms\Components\Section::make('Estado y Proceso')
                    ->description('Consulta el proceso y estado actual de la conversación.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('current_process')
                            ->disabled()
                            ->label('Proceso Actual'),
                        Forms\Components\TextInput::make('process_status')
                            ->disabled()
                            ->label('Estado del Proceso'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->searchable()
                    ->default('N/A')
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('chat_id')
                    ->searchable()
                    ->label('Número de Teléfono'),
                Tables\Columns\TextColumn::make('current_process')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->label('Proceso Actual')
                    ->default('General'),
                Tables\Columns\TextColumn::make('process_status')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->label('Estado del Proceso')
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Creado en')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Actualizado en')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            RelationManagersConversationMessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'create' => Pages\CreateConversation::route('/create'),
            'view' => Pages\ViewConversation::route('/{record}'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }
}
