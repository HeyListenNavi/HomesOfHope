<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rules\Unique;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $modelLabel = 'Pregunta';

    protected static ?string $pluralModelLabel = 'Preguntas';

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('stage_id')->relationship('stage', 'name')->required()->label('Etapa'),
                Forms\Components\Textarea::make('question_text')->required()->label('Pregunta'),
                Forms\Components\TextInput::make('order')->required()->numeric()->minValue(1)->label('Orden de la pregunta')->unique(
                    table: 'questions',
                    column: 'order',
                    ignoreRecord: true,
                    modifyRuleUsing: function (Unique $rule, Get $get) {
                        return $rule->where('stage_id', $get('stage_id'));
                    },
                ),
                Forms\Components\KeyValue::make('validation_rules')->label('Reglas de Validación'),
                Forms\Components\Repeater::make('approval_criteria')
                    ->schema([
                        Forms\Components\Select::make('rule')
                            ->label('Regla')
                            ->options([
                                'requerido' => 'Requerido',
                                'tipo' => 'Tipo',
                                'minimo' => 'Mínimo',
                                'maximo' => 'Máximo',
                                'tamano' => 'Tamaño',
                                'entre' => 'En (valores separados por comas)',
                                'aceptado' => 'Aceptado',
                                'otro' => 'Otro'
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('value')
                            ->label('Valor')
                            ->columnSpan(1),
                    ])
                    ->label('Criterios de Aprobación')
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question_text')->limit(50)->searchable()->label('Pregunta'),
                TextColumn::make('key')->searchable()->label('Clave única'),
                TextColumn::make('order')->label('Orden de la pregunta'),
                TextColumn::make('stage.name')->toggleable(isToggledHiddenByDefault: true)->label('Etapa'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultGroup('stage.name')
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
