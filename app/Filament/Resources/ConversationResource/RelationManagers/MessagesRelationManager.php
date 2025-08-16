<?php

namespace App\Filament\Resources\ConversationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConversationMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $recordTitleAttribute = 'message';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('message')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(100)
                    ->tooltip(fn($record) => $record->message)
                    ->wrap()
                    ->description(fn($record) => $record->created_at->diffForHumans(), position: 'below'),
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
