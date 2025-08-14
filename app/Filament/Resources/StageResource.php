<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StageResource\Pages;
use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                Forms\Components\TextInput::make('name')->required()->label('Nombre'),
                Forms\Components\TextInput::make('order')->required()->numeric()->gt(0)->unique(ignoreRecord: true)->label('Número de Etapa'),
                Forms\Components\Textarea::make('rejection_message')->label('Mensaje de Rechazo'),
                Forms\Components\Repeater::make('approval_criteria')
                    ->schema([
                        Forms\Components\Select::make('rule')
                            ->label('Regla')
                            ->options([
                                'requerido' => 'Requerido',
                                'numerico' => 'Numérico',
                                'correo_electronico' => 'Correo Electrónico',
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
                Forms\Components\Repeater::make('questions')
                    ->relationship('questions')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->label('Clave única')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Una clave única para identificar esta pregunta, ej. "nombre_completo".'),

                        Forms\Components\Textarea::make('question_text')
                            ->required()
                            ->label('Texto de la pregunta')
                            ->rows(3),

                        Forms\Components\TextInput::make('order')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->label('Orden de la pregunta'),
                    ])
                    ->label('Añadir Preguntas')
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull()
                    ->orderColumn('order')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre'),
                Tables\Columns\TextColumn::make('order')->sortable()->label('Orden de la Etapa'),
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
            ->reorderable('order')
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
