<?php

namespace App\Filament\Resources\VisitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EvidencesRelationManager extends RelationManager
{
    protected static string $relationship = 'evidences';

    protected static ?string $title = 'Evidencia';

    protected static ?string $icon = 'heroicon-o-camera';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cargar Evidencia')
                    ->description('Sube fotos o documentos de la visita.')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Archivo')
                            ->image() // O quita esto si aceptas PDFs
                            ->imageEditor()
                            ->directory('evidence-visits')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('taken_by')
                            ->default(Auth::id()),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_path')
            ->columns([
                // Vista previa de la imagen
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Evidencia')
                    ->square()
                    ->height(80),

                Tables\Columns\TextColumn::make('photographer.name')
                    ->label('Subido por')
                    ->icon('heroicon-o-user')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Subir Evidencia')
                    ->modalWidth('md'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                // Ver imagen en grande
                Tables\Actions\ViewAction::make()
                    ->form([
                         Forms\Components\FileUpload::make('file_path')
                            ->image()
                            ->columnSpanFull(),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}