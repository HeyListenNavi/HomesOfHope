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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
                Forms\Components\Section::make('Conversación')
                    ->description('Conversación a la que pertenece este mensaje.')
                    ->icon('heroicon-m-link')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('conversation_id')
                            ->relationship('conversation', 'chat_id')
                            ->label('Número de Telefono')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-m-chat-bubble-oval-left-ellipsis'),

                        Forms\Components\Select::make('role')
                            ->label('Rol')
                            ->options([
                                'user' => 'Usuario',
                                'assistant' => 'Bot',
                            ])
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-m-user'),
                    ]),

                Forms\Components\Section::make('Contenido')
                    ->schema([
                        Forms\Components\Textarea::make('message')
                            ->label('Mensaje')
                            ->required()
                            ->rows(5)
                            ->autosize()
                            ->columnSpanFull(),
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
                IconColumn::make('role')
                    ->label('Rol')
                    ->icon(fn(string $state): string => match ($state) {
                        'user' => 'heroicon-m-user',
                        'assistant' => 'heroicon-m-cpu-chip',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'user' => 'success',
                        'assistant' => 'info',
                        default => 'gray',
                    })
                    ->tooltip(fn(string $state): string => match ($state) {
                        'user' => 'Mensaje Entrante (Usuario)',
                        'assistant' => 'Mensaje Saliente (Bot)',
                        default => $state,
                    }),

                TextColumn::make('conversation.user_name')
                    ->label('Usuario')
                    ->searchable()
                    ->placeholder('Desconocido'),

                TextColumn::make('conversation.chat_id')
                    ->label('Número de Telefono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';

                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn($state) => 'https://wa.me/' . $state)
                    ->openUrlInNewTab()
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Contenido')
                    ->limit(60)
                    ->tooltip(fn(Message $record) => $record->message)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Creado En')
                    ->since()
                    ->color('gray')
                    ->icon('heroicon-m-clock')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filtrar por Rok')
                    ->options([
                        'user' => 'Entrantes (Usuarios)',
                        'assistant' => 'Salientes (Bot)',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
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
            'view' => Pages\ViewMessage::route('/{record}'),
        ];
    }
}
