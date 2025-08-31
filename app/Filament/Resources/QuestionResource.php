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
                Forms\Components\Section::make('Configuración de la Pregunta')
                    ->description('Selecciona la etapa y redacta la pregunta que se le hará al solicitante.')
                    ->schema([
                        Forms\Components\Select::make('stage_id')->relationship('stage', 'name')->required()->label('Etapa'),
                        Forms\Components\Textarea::make('question_text')->required()->label('Pregunta')->columnSpanFull()->rows(5)->autosize(),
                    ]),
                Forms\Components\Section::make('Criterios de Evaluación Automática')
                    ->description('Define las reglas que utilizará la IA para evaluar y aprobar la respuesta del solicitante.')
                    ->schema([
                        Forms\Components\Repeater::make('approval_criteria')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Select::make('rule')
                                    ->label('Regla')
                                    ->options([
                                        'approve_if' => 'Aprueba sí...',
                                        'reject_if' => 'No Aprueba sí...',
                                        'human_if' => 'Require de alguien del Equipo sí...',
                                    ])
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Select::make('operator')
                                    ->label('Operador')
                                    ->options([
                                        'Texto' => [
                                            'is' => 'es igual a',
                                            'is_not' => 'no es igual a',
                                            'contains' => 'contiene',

                                            'does_not_contain' => 'no contiene',
                                            'is_empty' => 'está vacío',
                                            'is_not_empty' => 'no está vacío',
                                        ],
                                        'Números' => [
                                            'is_equal_to' => 'es igual a',
                                            'is_greater_than' => 'es mayor que',
                                            'is_less_than' => 'es menor que',
                                            'is_greater_than_or_equal_to' => 'es mayor o igual que',
                                            'is_less_than_or_equal_to' => 'es menor o igual que',
                                            'between' => 'está entre',
                                        ],
                                        'Fechas' => [
                                            'is_before' => 'es anterior a',
                                            'is_after' => 'es posterior a',
                                        ],
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(function (Get $get) {
                                        return in_array($get('operator'), ['is_empty', 'is_not_empty']) ? '2' : '1';
                                    })
                                    ->reactive(),
                                Forms\Components\TextInput::make('value')
                                    ->label('Valor de la Regla')
                                    ->required()
                                    ->placeholder('Escribe el valor a comparar')
                                    ->hidden(function (Get $get) {
                                        return in_array($get('operator'), ['is_empty', 'is_not_empty']);
                                    }),
                            ])
                            ->label('Criterios de Aprobación')
                            ->defaultItems(0)
                            ->columns(3)
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question_text')->limit(50)->searchable()->label('Pregunta'),
                TextColumn::make('key')->searchable()->label('Clave única'),
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
