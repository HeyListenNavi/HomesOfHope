<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $modelLabel = 'Mensaje';

    protected static ?string $pluralModelLabel = 'Mensajes';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Auditoría y Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('conversation_id')
                    ->relationship('conversation', 'chat_id')
                    ->label('Número de Teléfono')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('chat_id')
                            ->required()
                            ->maxLength(255)
                            ->readOnly()
                            ->label('Número de Teléfono'),
                        Forms\Components\TextInput::make('user_name')
                            ->maxLength(255)
                            ->label('Nombre'),
                    ])
                    ->required(),
                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->options([
                        'user' => 'Usuario',
                        'assistant' => 'Bot',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->label('Mensaje')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('conversation.chat_id')
                    ->label('Número de Teléfono')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Número de Teléfono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'user' => 'Usuario',
                        'assistant' => 'Bot',
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'user' => 'success',
                        'assistant' => 'info',
                        default => 'secondary',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->searchable()
                    ->limit(50),
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
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
            'view' => Pages\ViewMessage::route('/{record}'),
            'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
}
