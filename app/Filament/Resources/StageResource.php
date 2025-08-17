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
                Forms\Components\TextInput::make('order')->required()->numeric()->minValue(1)->unique(ignoreRecord: true)->label('Número de Etapa'),
                Forms\Components\TextInput::make('name')->required()->label('Nombre')->columnSpan(3),
                Forms\Components\Textarea::make('rejection_message')->label('Mensaje de Rechazo')->rows(5)->autosize()->columnSpanFull(),
                Forms\Components\Repeater::make('questions')
                    ->relationship('questions')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->label('Número de pregunta'),
                        Forms\Components\Textarea::make('question_text')
                            ->required()
                            ->label('Pregunta')
                            ->columnSpan(3)
                            ->autosize(),
                    ])
                    ->label('Preguntas')
                    ->columns(4)
                    ->collapsible()
                    ->columnSpanFull()
                    ->orderColumn('order')
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre'),
                Tables\Columns\TextColumn::make('order')->sortable()->label('Número de Etapa'),
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
