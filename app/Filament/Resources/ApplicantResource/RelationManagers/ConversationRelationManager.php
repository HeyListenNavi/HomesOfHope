<?php

namespace App\Filament\Resources\ApplicantResource\RelationManagers;

use App\Enums\MessageRole;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConversationRelationManager extends RelationManager
{
    protected static string $relationship = 'conversation';

    protected static ?string $model = Message::class;

    protected static ?string $title = 'Historial del Chat';

    protected static ?string $icon = 'heroicon-m-chat-bubble-left-right';

    public function getTableQuery(): Builder
    {
        $applicant = $this->getOwnerRecord();

        if (! $applicant->conversation) {
            return Message::query()->whereNull('id');
        }

        return $applicant->conversation->messages()->getQuery();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalle del Mensaje')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Emisor')
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('message')
                            ->label('Contenido')
                            ->required()
                            ->rows(4)
                            ->autosize()
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('role')
                    ->label('Rol')
                    ->tooltip(fn (MessageRole $state): string => $state->getLabel()),

                TextColumn::make('message')
                    ->label('Mensaje')
                    ->color(fn (Message $record) => $record->role === MessageRole::Assistant ? 'gray' : 'black')
                    ->limit(150)
                    ->wrap()
                    ->searchable()
                    ->description(fn (Message $record) => $record->created_at->locale('es')->diffForHumans(), position: 'below'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filtrar por Emisor'),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading(''),
                ])
                    ->color('gray'),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateHeading('Sin mensajes')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-ellipsis');
    }
}
