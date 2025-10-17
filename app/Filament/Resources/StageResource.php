<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StageResource\Pages;
use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;
    protected static ?string $modelLabel = 'Etapa';
    protected static ?string $pluralModelLabel = 'Etapas';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Sección para la información principal de la etapa
                Forms\Components\Section::make('Información Principal')
                    ->description('Define el nombre y el orden de la etapa en el proceso.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nombre')
                    ])->columns(1),

                // Usamos Tabs para organizar los diferentes mensajes y ahorrar espacio vertical
                Forms\Components\Section::make('Mensajes Automatizados')
                    ->description('Configura los mensajes que se mostrarán en diferentes momentos de la etapa.')
                    ->schema([
                        Forms\Components\Tabs::make('Mensajes')->tabs([
                            Forms\Components\Tabs\Tab::make('Mensaje de Aprobación')
                                ->icon('heroicon-o-check-circle')
                                ->schema([
                                    Forms\Components\Textarea::make('approval_message')
                                        ->label('Mensaje de aprobación de la etapa')
                                        ->helperText('Este mensaje se muestra si la etapa es aprobada.')
                                        ->rows(5)
                                        ->autosize(),
                                ]),
                            Forms\Components\Tabs\Tab::make('Mensaje de Rechazo')
                                ->icon('heroicon-o-x-circle')
                                ->schema([
                                    Forms\Components\Textarea::make('rejection_message')
                                        ->label('Mensaje de rechazo de la etapa')
                                        ->helperText('Este mensaje se muestra si la etapa es rechazada.')
                                        ->rows(5)
                                        ->autosize(),
                                ]),
                            Forms\Components\Tabs\Tab::make('Mensaje de Evaluación')
                                ->icon('heroicon-o-magnifying-glass-circle')
                                ->schema([
                                    Forms\Components\Textarea::make('requires_evaluatio_message')
                                        ->label('Mensaje para solicitar evaluación')
                                        ->helperText('Este mensaje se muestra cuando se necesita una evaluación manual.')
                                        ->rows(5)
                                        ->autosize(),
                                ]),
                        ]),
                    ])->collapsible(),
                
                // El Repeater para las preguntas, ahora dentro de su propia sección
                Forms\Components\Section::make('Preguntas de la Etapa')
                    ->description('Añade, elimina y reordena las preguntas que conformarán esta etapa.')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->relationship('questions')
                            ->schema([
                                Forms\Components\Textarea::make('question_text')
                                    ->required()
                                    ->label('Texto de la Pregunta')
                                    ->autosize(),
                            ])
                            ->label('Preguntas')
                            ->columns(1)
                            ->collapsible()
                            ->orderColumn('order') // Permite reordenar arrastrando y soltando
                            ->defaultItems(1) // Inicia con un item por defecto
                            ->addActionLabel('Añadir Pregunta'),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->searchable()
                    ->label('Orden'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Nº Preguntas')
                    ->sortable(),
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
            ->reorderable('order') // Habilita el reordenamiento en la tabla
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
            'index' => Pages\ListStages::route('/'),
            'create' => Pages\CreateStage::route('/create'),
            'edit' => Pages\EditStage::route('/{record}/edit'),
        ];
    }
}
