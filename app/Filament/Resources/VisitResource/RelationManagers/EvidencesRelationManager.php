<?php

namespace App\Filament\Resources\VisitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EvidencesRelationManager extends RelationManager
{
    protected static string $relationship = 'evidences';

    protected static ?string $title = 'Evidencias'; // Título más completo

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
                            ->disk('r2')
                            ->directory('evidence-visits')
                            ->required()
                            ->columnSpanFull()
                            ->downloadable() // Permite descargar al editar
                            ->openable() // Permite abrir en nueva pestaña
                            ->previewable(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->autosize()
                            ->placeholder('Notas sobre esta evidencia...'),

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
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Vista Previa')
                    ->disk('r2')
                    ->visibility('private')
                    ->square()
                    ->size(240)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover shadow-sm']),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Actions\Action::make('view')
                    ->label('')
                    ->icon('heroicon-s-document')
                    ->tooltip('Visualizar')
                    ->url(fn ($record) => Storage::disk('r2')->temporaryUrl($record->file_path, now()->addMinutes(5)))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download')
                    ->label('')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->tooltip('Descargar')
                    ->url(fn ($record) => Storage::disk('r2')->temporaryUrl(
                        $record->file_path,
                        now()->addMinutes(5),
                        ['ResponseContentDisposition' => 'attachment; filename="'.($record->original_name ?? 'documento').'"']
                    ))
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-s-eye')
                    ->modalWidth('4xl'),

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
