<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource\RelationManagers;
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

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Auditoría y Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('chat_id')
                    ->required()
                    ->maxLength(255)
                    ->readOnly(),
                Forms\Components\TextInput::make('user_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('current_process')
                    ->maxLength(255),
                Forms\Components\TextInput::make('process_status')
                    ->maxLength(255),
                Forms\Components\TextInput::make('process_id')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->searchable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('chat_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_process')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->default('General'),
                Tables\Columns\TextColumn::make('process_status')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('process_id')
                    ->numeric()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
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
            //
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
