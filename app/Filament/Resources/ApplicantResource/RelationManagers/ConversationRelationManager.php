<?php

namespace App\Filament\Resources\ApplicantResource\RelationManagers;

use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApplicantConversationRelationManager extends RelationManager
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
                            ->options([
                                'user' => 'Usuario (Aplicante)',
                                'assistant' => 'Bot (Sistema)',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('message')
                            ->label('Contenido')
                            ->required()
                            ->rows(4)
                            ->autosize()
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ])
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
                    ->icon(fn (string $state): string => match ($state) {
                        'user' => 'heroicon-m-user',
                        'assistant' => 'heroicon-m-cpu-chip', 
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'user' => 'info',
                        'assistant' => 'primary',
                        default => 'gray',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'user' => 'Enviado por el Usuario',
                        'assistant' => 'Respuesta del Bot',
                        default => $state,
                    }),

                TextColumn::make('message')
                    ->label('Mensaje')
                    ->color(fn (Message $record) => $record->role === 'assistant' ? 'gray' : 'black')
                    ->limit(150)
                    ->wrap()
                    ->searchable()
                    ->description(fn (Message $record) => $record->created_at->locale('es')->diffForHumans(), position: 'below'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filtrar por Emisor')
                    ->options([
                        'user' => 'Usuario',
                        'assistant' => 'Bot',
                    ]),
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
            ->emptyStateDescription('No se ha iniciado ninguna conversación con este aplicante.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-ellipsis');
    }
}