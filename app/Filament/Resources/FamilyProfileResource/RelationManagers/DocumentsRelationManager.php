<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use App\Enums\DocumentType;
use App\Models\Document;
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
                                        Forms\Components\Select::make('target')
                                            ->label('Pertenece a')
                                            ->options(function (): array {
                                                $owner = $this->getOwnerRecord();
                                                $options = [
                                                    'profile' => 'Familia (General)',
                                                ];

                                                foreach ($owner->members as $member) {
                                                    $relation = $member->relationship?->getLabel() ?? 'Miembro';
                                                    $options['member_'.$member->id] = "{$relation}: {$member->full_name}";
                                                }

                                                return $options;
                                            })
                                            ->default('profile')
                                            ->required()
                                            ->native(false)
                                            ->formatStateUsing(function (?Document $record): string {
                                                if (! $record) {
                                                    return 'profile';
                                                }

                                                return $record->documentable_type === FamilyMember::class
                                                    ? 'member_'.$record->documentable_id
                                                    : 'profile';
                                            }),

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

    protected function getTableQuery(): Builder
    {
        $familyProfileId = $this->getOwnerRecord()->id;

        return Document::query()
            ->where(function (Builder $query) use ($familyProfileId) {
                $query->where(function (Builder $q) use ($familyProfileId) {
                    $q->where('documentable_type', FamilyProfile::class)
                        ->where('documentable_id', $familyProfileId);
                })->orWhere(function (Builder $q) use ($familyProfileId) {
                    $q->where('documentable_type', FamilyMember::class)
                        ->whereIn('documentable_id', FamilyMember::where('family_profile_id', $familyProfileId)->select('id'));
                });
            })
            ->with(['documentable', 'uploader']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordAction('preview')
            ->columns([
                Tables\Columns\TextColumn::make('original_name')
                    ->label('Nombre del Archivo')
                    ->searchable()
                    ->limit(30)
                    ->icon(fn (Document $record): ?string => $record->preview_type->getIcon())
                    ->color('gray')
                    ->description(function (Document $record) {
                        if ($record->documentable_type === FamilyMember::class && $record->documentable) {
                            return 'Familiar: '.($record->documentable->full_name ?? 'Miembro');
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
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state / 1024, 2).' KB' : '0 KB')
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Subido por')
                    ->icon('heroicon-s-user')
                    ->formatStateUsing(fn ($state): string => $state ?? 'Sistema')
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
                    ->using(function (array $data): Document {
                        $target = $data['target'] ?? 'profile';
                        unset($data['target']);

                        $data = $this->processFileMetadata($data);

                        if (str_starts_with($target, 'member_')) {
                            $data['documentable_type'] = FamilyMember::class;
                            $data['documentable_id'] = (int) str_replace('member_', '', $target);
                        } else {
                            $data['documentable_type'] = FamilyProfile::class;
                            $data['documentable_id'] = $this->getOwnerRecord()->id;
                        }

                        return Document::create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label(false)
                    ->modalHeading('Visualizar Documento')
                    ->modalDescription(fn (Document $record) => ($record->document_type?->getLabel() ?? 'Documento').' • '.($record->documentable_type === FamilyMember::class ? ('Familiar: '.($record->documentable?->full_name ?? 'Miembro')) : 'Familia (General)'))
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(fn (Document $record) => view('filament.components.document-preview-modal', [
                        'record' => $record,
                        'url' => Storage::disk('r2')->temporaryUrl($record->file_path, now()->addMinutes(30)),
                    ]))
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('openInNewTab')
                            ->label('Abrir en pestaña')
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->color('gray')
                            ->url(fn (Document $record) => Storage::disk('r2')->temporaryUrl($record->file_path, now()->addMinutes(30)))
                            ->openUrlInNewTab(),
                        Tables\Actions\Action::make('downloadFile')
                            ->label('Descargar')
                            ->icon('heroicon-m-arrow-down-tray')
                            ->color('primary')
                            ->url(fn (Document $record) => Storage::disk('r2')->temporaryUrl(
                                $record->file_path,
                                now()->addMinutes(30),
                                ['ResponseContentDisposition' => 'attachment; filename="'.($record->original_name ?? 'documento').'"']
                            ))
                            ->openUrlInNewTab(),
                    ]),

                Tables\Actions\Action::make('download')
                    ->label('')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->color('gray')
                    ->tooltip('Descargar archivo')
                    ->url(fn (Document $record) => Storage::disk('r2')->temporaryUrl(
                        $record->file_path,
                        now()->addMinutes(30),
                        ['ResponseContentDisposition' => 'attachment; filename="'.($record->original_name ?? 'documento').'"']
                    ))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->using(function (Document $record, array $data): Document {
                        $target = $data['target'] ?? 'profile';
                        unset($data['target']);

                        $data = $this->processFileMetadata($data);

                        if (str_starts_with($target, 'member_')) {
                            $data['documentable_type'] = FamilyMember::class;
                            $data['documentable_id'] = (int) str_replace('member_', '', $target);
                        } else {
                            $data['documentable_type'] = FamilyProfile::class;
                            $data['documentable_id'] = $this->getOwnerRecord()->id;
                        }

                        $record->update($data);

                        return $record;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Tipo de Documento')
                    ->options(DocumentType::class),
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
        $path = $data['file_path'] ?? null;

        if ($path && $disk->exists($path)) {
            $data['mime_type'] = $disk->mimeType($path);
            $data['size'] = $disk->size($path);
        }

        return $data;
    }
}
