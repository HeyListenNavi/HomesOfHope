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
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn\TextColumnFontFamily;

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
                Forms\Components\Section::make('Definición de la Pregunta')
                    ->description('Configura el contenido y la etapa a la que pertenece.')
                    ->icon('heroicon-m-chat-bubble-bottom-center-text')
                    ->columns(1)
                    ->schema([
                        Forms\Components\Select::make('stage_id')
                            ->relationship('stage', 'name')
                            ->required()
                            ->label('Etapa del Proceso')
                            ->prefixIcon('heroicon-m-rectangle-stack')
                            ->native(false),

                        Forms\Components\Textarea::make('question_text')
                            ->required()
                            ->label('Pregunta')
                            ->placeholder('¿Cuál es tu ingreso mensual aproximado?')
                            ->rows(3)
                            ->autosize(),

                        Forms\Components\Toggle::make('show_in_table')
                            ->label('Mostrar en tabla de Aplicantes')
                            ->helperText('Crea una columna en la vista principal para leer estas respuestas sin tener que entrar al perfil.')
                            ->default(false),
                    ]),

                Forms\Components\Section::make('Lógica de Evaluación (IA)')
                    ->description('Reglas automáticas para validar la respuesta del aplicante.')
                    ->icon('heroicon-m-cpu-chip')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Repeater::make('approval_criteria')
                            ->label('Reglas')
                            ->addActionLabel('Agregar nueva regla')
                            ->itemLabel('Regla')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('rule')
                                            ->label('Acción')
                                            ->options([
                                                'approve_if' => 'Aprobar automáticamente sí...',
                                                'reject_if' => 'Rechazar automáticamente sí...',
                                                'human_if' => 'Solicitar revisión humana sí...',
                                            ])
                                            ->prefixIcon('heroicon-m-play')
                                            ->required(),

                                        Forms\Components\Select::make('operator')
                                            ->label('Condición')
                                            ->options([
                                                'Texto' => [
                                                    'is' => 'es igual a',
                                                    'is_not' => 'no es igual a',
                                                    'contains' => 'contiene la palabra',
                                                    'does_not_contain' => 'no contiene',
                                                    'is_empty' => 'está vacío',
                                                    'is_not_empty' => 'tiene contenido',
                                                ],
                                                'Números' => [
                                                    'is_equal_to' => '= igual a',
                                                    'is_greater_than' => '> mayor que',
                                                    'is_less_than' => '< menor que',
                                                    'is_greater_than_or_equal_to' => '>= mayor o igual',
                                                    'is_less_than_or_equal_to' => '<= menor o igual',
                                                    'between' => 'está entre rango',
                                                ],
                                            ])
                                            ->required()
                                            ->searchable()
                                            ->columnSpan(fn (Get $get) => in_array($get('operator'), ['is_empty', 'is_not_empty']) ? 2 : 1)
                                            ->reactive(),

                                        Forms\Components\TextInput::make('value')
                                            ->label('Valor de Comparación')
                                            ->required()
                                            ->placeholder('Valor...')
                                            ->hidden(fn (Get $get) => in_array($get('operator'), ['is_empty', 'is_not_empty'])),
                                    ]),
                            ])
                            ->collapsible()
                            ->cloneable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50, 100])
            ->defaultGroup('stage.name')
            ->defaultSort('order', 'asc')
            ->columns([
                TextColumn::make('question_text')
                    ->label('Pregunta')
                    ->limit(90)
                    ->tooltip(fn ($record) => $record->question_text)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('stage.name')
                    ->label('Etapa')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('show_in_table')
                    ->label('Mostrar en tabla')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->color('gray'),
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
