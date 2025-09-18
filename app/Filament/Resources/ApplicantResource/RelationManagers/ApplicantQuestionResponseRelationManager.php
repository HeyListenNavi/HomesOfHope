<?php

namespace App\Filament\Resources\ApplicantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicantQuestionResponseRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('question_text_snapshot')
                    ->label('Pregunta')
                    ->columnSpanFull()
                    ->required(),

                Forms\Components\Textarea::make('user_response')
                    ->label('Respuesta')
                    ->columnSpanFull()
                    ->rows(5)
                    ->autosize()
                    ->required(),

                Forms\Components\Select::make('ai_decision')
                    ->label('Decisión de la IA')
                    ->options([
                        'valid' => 'Válido',
                        'not_valid' => 'No Válido',
                        'requires_supervision' => 'Requiere Supervisión',
                    ])
                    ->columnSpanFull()
                    ->reactive()
                    ->required(),

                Forms\Components\Textarea::make('ai_decision_reason')
                    ->label('Análisis generado por la IA')
                    ->columnSpanFull()
                    ->rows(5)
                    ->autosize()
                    ->default('No hubo análisis, la respuesta fue aprobada')
                    ->visible(function (Get $get): bool {
                        return in_array($get('ai_decision'), ['not_valid', 'requires_supervision']);
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text_snapshot')
            ->columns([
                Tables\Columns\IconColumn::make('ai_decision')
                    ->label('Decisión IA')
                    ->icon(fn(string $state): string => match ($state) {
                        'valid' => 'heroicon-o-check-circle',
                        'requires_revision' => 'heroicon-o-exclamation-triangle',
                        'not_valid' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'valid' => 'success',
                        'requires_human_revision' => 'warning',
                        'not_valid' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('question_text_snapshot')
                    ->label('Pregunta')
                    ->limit(60) 
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_response')
                    ->label('Respuesta')
                    ->limit(80) 
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->searchable(),
            ])
            ->defaultGroup('question.stage.name')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ]);
    }
}
