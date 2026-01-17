<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documentos y Archivos';

    protected static ?string $icon = 'heroicon-s-document-duplicate';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // COLUMNA IZQUIERDA
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 2])
                            ->schema([
                                Forms\Components\Section::make('Carga de Archivo')
                                    ->description('Sube documentos legales, identificaciones o reportes.')
                                    ->icon('heroicon-s-arrow-up-tray')
                                    ->schema([
                                        Forms\Components\FileUpload::make('file_path')
                                            ->label('Seleccionar Archivo')
                                            ->required()
                                            ->disk('public')
                                            ->directory('documents')
                                            ->storeFileNamesIn('original_name')
                                            ->preserveFilenames(false)
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->maxSize(25600)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // COLUMNA DERECHA
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Section::make('Clasificación')
                                    ->icon('heroicon-s-tag')
                                    ->schema([
                                        Forms\Components\Select::make('document_type')
                                            ->label('Tipo de Documento')
                                            ->options([
                                                'ine' => '🆔 INE / Identificación',
                                                'curp' => '📄 CURP',
                                                'proof_of_address' => '🏠 Comprobante Domicilio',
                                                'contract' => '✍️ Contrato',
                                                'report' => '📊 Reporte / Estudio',
                                                'photo' => '📷 Fotografía',
                                                'other' => '📂 Otro',
                                            ])
                                            ->required()
                                            ->native(false)
                                            ->searchable(),

                                        // Campos ocultos
                                        Forms\Components\Hidden::make('original_name'),
                                        Forms\Components\Hidden::make('mime_type'),
                                        Forms\Components\Hidden::make('size'),

                                        Forms\Components\Hidden::make('uploaded_by')
                                            ->default(Auth::id()),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('original_name')
                    ->label('Nombre del Archivo')
                    ->searchable()
                    ->limit(30)
                    ->icon(fn($record) => match (explode('/', $record->mime_type ?? '')[0] ?? '') {
                        'image' => 'heroicon-s-photo',
                        'application' => 'heroicon-s-document-text',
                        'text' => 'heroicon-s-document-text',
                        default => 'heroicon-s-paper-clip',
                    })
                    ->color('gray'),

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ine' => 'INE',
                        'curp' => 'CURP',
                        'proof_of_address' => 'Domicilio',
                        'contract' => 'Contrato',
                        'report' => 'Reporte',
                        'photo' => 'Foto',
                        'other' => 'Otro',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'ine', 'curp', 'proof_of_address' => 'info',
                        'contract' => 'success',
                        'report' => 'warning',
                        'photo' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn($state) => $state ? number_format($state / 1024, 2) . ' KB' : '0 KB')
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploaded_by.name')
                    ->label('Subido por')
                    ->icon('heroicon-s-user')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Subir Documento')
                    ->icon('heroicon-s-arrow-up-tray')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        return $this->processFileMetadata($data);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->tooltip('Descargar')
                    ->url(fn($record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        return $this->processFileMetadata($data);
                    }),

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

    protected function processFileMetadata(array $data): array
    {
        $disk = Storage::disk('public');
        $path = $data['file_path'];

        if ($disk->exists($path)) {
            $data['mime_type'] = $disk->mimeType($path);
            $data['size'] = $disk->size($path);
        }

        return $data;
    }
}
