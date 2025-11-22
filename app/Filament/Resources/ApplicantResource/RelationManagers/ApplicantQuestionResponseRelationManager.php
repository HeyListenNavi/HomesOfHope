<?php

namespace App\Filament\Resources\ApplicantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class ApplicantQuestionResponseRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    protected static ?string $title = 'Respuestas';
    protected static ?string $icon = 'heroicon-m-chat-bubble-left-right';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contenido de la Respuesta')
                    ->description('Revisión de la pregunta realizada y la respuesta capturada.')
                    ->icon('heroicon-m-document-text')
                    ->columns(1)
                    ->schema([
                        Forms\Components\Textarea::make('question_text_snapshot')
                            ->label('Pregunta Realizada')
                            ->autosize(),

                        Forms\Components\Textarea::make('user_response')
                            ->label('Respuesta del Usuario')
                            ->rows(4)
                            ->autosize()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Análisis de Inteligencia Artificial')
                    ->description('Validación automática y razonamiento.')
                    ->icon('heroicon-m-cpu-chip')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('ai_decision')
                            ->label('Decisión de la IA')
                            ->options([
                                'valid' => 'Válido',
                                'not_valid' => 'No Válido',
                                'requires_supervision' => 'Requiere Supervisión',
                            ])
                            ->prefixIcon('heroicon-m-scale'),

                        Forms\Components\Textarea::make('ai_decision_reason')
                            ->label('Razonamiento de la IA')
                            ->rows(3)
                            ->autosize()
                            ->visible(fn(Get $get) => in_array($get('ai_decision'), ['not_valid', 'requires_supervision']))
                            ->helperText('Explica por qué la IA marcó esta respuesta como inválida o dudosa.'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text_snapshot')
            ->defaultSort('created_at', 'desc')
            ->defaultGroup('question.stage.name')
            ->columns([
                TextColumn::make('ai_decision')
                    ->label('Estatus IA')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'valid' => 'Válido',
                        'not_valid' => 'Inválido',
                        'requires_supervision' => 'Revisión',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'valid' => 'success',
                        'requires_supervision' => 'warning',
                        'not_valid' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'valid' => 'heroicon-m-check-circle',
                        'requires_supervision' => 'heroicon-m-eye',
                        'not_valid' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-minus',
                    })
                    ->sortable(),

                TextColumn::make('question_text_snapshot')
                    ->label('Pregunta')
                    ->color('gray')
                    ->limit(90)
                    ->tooltip(fn(Model $record) => $record->question_text_snapshot)
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ai_decision')
                    ->label('Filtrar por Decisión')
                    ->options([
                        'valid' => 'Válido',
                        'not_valid' => 'No Válido',
                        'requires_supervision' => 'Requiere Supervisión',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar Respuesta Manual'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading(''),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
