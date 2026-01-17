<?php

namespace App\Filament\Resources\VisitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class EvidencesRelationManager extends RelationManager
{
    protected static string $relationship = 'evidences';

    protected static ?string $title = 'Evidencia y Archivos'; // Título más completo

    protected static ?string $icon = 'heroicon-s-camera'; // Icono sólido

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cargar Evidencia')
                    ->description('Sube fotografías o documentos que validen la visita.')
                    ->icon('heroicon-s-arrow-up-tray')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Archivo de Evidencia')
                            ->image() // Priorizamos imágenes
                            ->imageEditor() // Permite recortar/rotar antes de subir
                            ->disk('public')
                            ->directory('evidence-visits')
                            ->required()
                            ->columnSpanFull()
                            ->downloadable() // Permite descargar al editar
                            ->openable() // Permite abrir en nueva pestaña
                            ->previewable(),

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
                // Imagen con estilo redondeado
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Vista Previa')
                    ->square()
                    ->size(80)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover shadow-sm']),

                Tables\Columns\TextColumn::make('photographer.name')
                    ->label('Subido por')
                    ->icon('heroicon-s-user')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Subir Evidencia')
                    ->icon('heroicon-s-arrow-up-tray')
                    ->slideOver() // Panel lateral es mejor para cargas
                    ->modalWidth('md'),
            ])
            ->actions([
                // Acción rápida para descargar sin abrir el registro
                Tables\Actions\Action::make('download')
                    ->label('')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->tooltip('Descargar')
                    ->url(fn ($record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-s-eye')
                    ->modalWidth('xl'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
