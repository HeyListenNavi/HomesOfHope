<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource\RelationManagers\ConversationMessagesRelationManager;
use App\Filament\Resources\ConversationResource\RelationManagers\MessagesRelationManager;
use App\Models\Conversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $modelLabel = 'Conversación';

    protected static ?string $pluralModelLabel = 'Conversaciones';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Auditoría y Logs';

    protected static ?string $recordTitleAttribute = 'user_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['user_name', 'chat_id'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->user_name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Teléfono' => str_starts_with($record->chat_id, '521') ? substr($record->chat_id, 3) : $record->chat_id,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Contacto')
                    ->description('Identificación del usuario en el chat.')
                    ->icon('heroicon-m-user-circle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('user_name')
                            ->label('Nombre')
                            ->prefixIcon('heroicon-m-user')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('chat_id')
                            ->label('Número de Telefono')
                            ->required()
                            ->prefixIcon('heroicon-m-phone')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Estado del Bot')
                    ->description('Rastreo del flujo y estado actual del sistema.')
                    ->icon('heroicon-m-cpu-chip')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('current_process')
                            ->label('Proceso Actual')
                            ->disabled()
                            ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),

                        Forms\Components\TextInput::make('process_status')
                            ->label('Estatus')
                            ->disabled()
                            ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user_name')
                    ->label('Usuario')
                    ->searchable()
                    ->default('Desconocido'),

                TextColumn::make('chat_id')
                    ->label('Número de Telefono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';

                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn($state) => 'https://wa.me/' . $state)
                    ->openUrlInNewTab()
                    ->searchable(),

                TextColumn::make('current_process')
                    ->label('Proceso')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->searchable(),

                TextColumn::make('process_status')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->color('gray')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->color('gray'),
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
            MessagesRelationManager::class,
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
