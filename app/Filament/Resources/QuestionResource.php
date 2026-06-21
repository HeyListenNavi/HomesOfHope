<?php

namespace App\Filament\Resources;

use App\Enums\ApprovalRule;
use App\Enums\NumericOperator;
use App\Enums\TextOperator;
use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                                            ->options(ApprovalRule::class)
                                            ->prefixIcon('heroicon-m-play')
                                            ->required(),

                                        Forms\Components\Select::make('operator')
                                            ->label('Condición')
                                            ->options([
                                                'Texto' => collect(TextOperator::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->toArray(),
                                                'Números' => collect(NumericOperator::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->toArray(),
                                            ])
                                            ->required()
                                            ->searchable()
                                            ->columnSpan(fn (Get $get) => in_array($get('operator'), [TextOperator::IsEmpty->value, TextOperator::IsNotEmpty->value]) ? 2 : 1)
                                            ->reactive(),

                                        Forms\Components\TextInput::make('value')
                                            ->label('Valor de Comparación')
                                            ->required()
                                            ->placeholder('Valor...')
                                            ->hidden(fn (Get $get) => in_array($get('operator'), [TextOperator::IsEmpty->value, TextOperator::IsNotEmpty->value])),
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
