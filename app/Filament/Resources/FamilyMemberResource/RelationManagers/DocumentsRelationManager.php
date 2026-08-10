<?php

namespace App\Filament\Resources\FamilyMemberResource\RelationManagers;

use App\Enums\DocumentType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documentos y Archivos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->columnSpan(['lg' => 2])
                            ->schema([
                                Section::make('Carga de Archivo')
                                    ->description('Sube documentos legales, identificaciones o reportes.')
                                    ->icon('heroicon-s-arrow-up-tray')
                                    ->schema([
                                        FileUpload::make('file_path')
                                            ->label('Seleccionar Archivo')
                                            ->required()
                                            ->disk('r2')
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

                        Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Section::make('Clasificación')
                                    ->icon('heroicon-s-tag')
                                    ->schema([
                                        Select::make('document_type')
                                            ->label('Tipo de Documento')
                                            ->options(DocumentType::class)
                                            ->required()
                                            ->native(false)
                                            ->searchable(),

                                        Hidden::make('original_name'),
                                        Hidden::make('mime_type'),
                                        Hidden::make('size'),

                                        Hidden::make('uploaded_by')
                                            ->default(Auth::id()),
                                    ]),
                            ]),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull()
                            ->autosize()
                            ->placeholder('Notas adicionales sobre este documento...'),
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
                    ->icon(fn ($record) => match (explode('/', $record->mime_type ?? '')[0] ?? '') {
                        'image' => 'heroicon-s-photo',
                        'application' => 'heroicon-s-document-text',
                        'text' => 'heroicon-s-document-text',
                        default => 'heroicon-s-paper-clip',
                    })
                    ->color('gray'),

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Tipo')
                    ->badge(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2).' KB' : '0 KB')
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploaded_by.name')
                    ->label('Subido por')
                    ->icon('heroicon-s-user')
                    ->formatStateUsing(fn ($state) => $state ?? 'Sistema')
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
                Tables\Actions\Action::make('view')
                    ->label('')
                    ->icon('heroicon-s-eye')
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
        $disk = Storage::disk('r2');
        $path = $data['file_path'];

        if ($disk->exists($path)) {
            $data['mime_type'] = $disk->mimeType($path);
            $data['size'] = $disk->size($path);
        }

        return $data;
    }
}
