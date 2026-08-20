<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use App\Enums\DocumentType;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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

                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Section::make('Clasificación')
                                    ->icon('heroicon-s-tag')
                                    ->schema([
                                        Forms\Components\Select::make('document_type')
                                            ->label('Tipo de Documento')
                                            ->options(DocumentType::class)
                                            ->required()
                                            ->native(false)
                                            ->searchable(),

                                        Forms\Components\Hidden::make('original_name'),
                                        Forms\Components\Hidden::make('mime_type'),
                                        Forms\Components\Hidden::make('size'),

                                        Forms\Components\Hidden::make('uploaded_by')
                                            ->default(Auth::id()),
                                    ]),
                            ]),

                        Forms\Components\Textarea::make('description')
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
            ->modifyQueryUsing(function (Builder $query) {
                $familyProfileId = $this->getOwnerRecord()->id;

                $query->where(function ($q) use ($familyProfileId) {
                    $q->where('documentable_type', FamilyProfile::class)
                        ->where('documentable_id', $familyProfileId);
                })
                    ->orWhere(function ($q) use ($familyProfileId) {
                        $q->where('documentable_type', FamilyMember::class)
                            ->whereHasMorph('documentable', [FamilyMember::class], function ($query) use ($familyProfileId) {
                                $query->where('family_profile_id', $familyProfileId);
                            });
                    });
            })
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
                    ->color('gray')
                    ->description(function ($record) {
                        if ($record->documentable_type === FamilyMember::class) {
                            return 'De: '.($record->documentable->full_name ?? 'Familiar');
                        }

                        return null;
                    }),

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
