<?php

namespace App\Filament\Resources\VisitResource\RelationManagers;

use App\Enums\FilePreviewType;
use App\Models\Evidence;
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

    protected static ?string $title = 'Evidencias';

    protected static ?string $icon = 'heroicon-s-camera';

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
                                    ->description('Sube fotografías, audios, videos o reportes de la visita.')
                                    ->icon('heroicon-s-arrow-up-tray')
                                    ->schema([
                                        Forms\Components\FileUpload::make('file_path')
                                            ->label('Seleccionar Archivo(s)')
                                            ->multiple(fn (string $operation): bool => $operation === 'create')
                                            ->disk('r2')
                                            ->visibility('private')
                                            ->directory('evidence-visits')
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->maxSize(51200)
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Section::make('Información')
                                    ->icon('heroicon-s-information-circle')
                                    ->schema([
                                        Forms\Components\Hidden::make('taken_by')
                                            ->default(Auth::id()),
                                        Forms\Components\Hidden::make('mime_type'),
                                    ]),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull()
                            ->autosize()
                            ->placeholder('Notas sobre esta evidencia...'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordAction('preview')
            ->columns([
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Nombre del Archivo')
                    ->searchable()
                    ->limit(30)
                    ->formatStateUsing(fn (string $state): string => basename($state))
                    ->icon(fn (Evidence $record): ?string => $this->getPreviewType($record)->getIcon())
                    ->color('gray'),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (Evidence $record): string => $this->getPreviewType($record)->getLabel())
                    ->color(fn (Evidence $record): string => $this->getPreviewType($record)->getColor()),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('photographer.name')
                    ->label('Subido por')
                    ->icon('heroicon-s-user')
                    ->formatStateUsing(fn ($state): string => $state ?? 'Sistema')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Subir Evidencia')
                    ->icon('heroicon-s-arrow-up-tray')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->using(function (array $data): Evidence {
                        $paths = (array) ($data['file_path'] ?? []);
                        $disk = Storage::disk('r2');
                        $firstRecord = null;

                        foreach ($paths as $path) {
                            $mimeType = $disk->exists($path) ? $disk->mimeType($path) : null;

                            $evidence = Evidence::create([
                                'visit_id' => $data['visit_id'] ?? $this->getOwnerRecord()->id,
                                'file_path' => $path,
                                'mime_type' => $mimeType,
                                'description' => $data['description'] ?? null,
                                'taken_by' => $data['taken_by'] ?? Auth::id(),
                            ]);

                            $firstRecord ??= $evidence;
                        }

                        return $firstRecord;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label(false)
                    ->modalHeading(fn (Evidence $record) => basename($record->file_path))
                    ->modalDescription(fn (Evidence $record) => $this->getPreviewType($record)->getLabel().' • Evidencia de Visita')
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(function (Evidence $record) {
                        $record->preview_type = $this->getPreviewType($record);
                        $record->original_name = basename($record->file_path);

                        return view('filament.components.document-preview-modal', [
                            'record' => $record,
                            'url' => Storage::disk('r2')->temporaryUrl($record->file_path, now()->addMinutes(30)),
                        ]);
                    })
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('openInNewTab')
                            ->label('Abrir en pestaña')
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->color('gray')
                            ->url(fn (Evidence $record) => Storage::disk('r2')->temporaryUrl($record->file_path, now()->addMinutes(30)))
                            ->openUrlInNewTab(),
                        Tables\Actions\Action::make('downloadFile')
                            ->label('Descargar')
                            ->icon('heroicon-m-arrow-down-tray')
                            ->color('primary')
                            ->url(fn (Evidence $record) => Storage::disk('r2')->temporaryUrl(
                                $record->file_path,
                                now()->addMinutes(30),
                                ['ResponseContentDisposition' => 'attachment; filename="'.basename($record->file_path).'"']
                            ))
                            ->openUrlInNewTab(),
                    ]),

                Tables\Actions\Action::make('download')
                    ->label('')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->color('gray')
                    ->tooltip('Descargar archivo')
                    ->url(fn (Evidence $record) => Storage::disk('r2')->temporaryUrl(
                        $record->file_path,
                        now()->addMinutes(30),
                        ['ResponseContentDisposition' => 'attachment; filename="'.basename($record->file_path).'"']
                    ))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->using(function (Evidence $record, array $data): Evidence {
                        $path = is_array($data['file_path']) ? reset($data['file_path']) : $data['file_path'];
                        $disk = Storage::disk('r2');

                        $updateData = [
                            'description' => $data['description'] ?? null,
                        ];

                        if (isset($data['visit_id'])) {
                            $updateData['visit_id'] = $data['visit_id'];
                        }

                        if ($path && $path !== $record->file_path) {
                            $updateData['file_path'] = $path;
                            if ($disk->exists($path)) {
                                $updateData['mime_type'] = $disk->mimeType($path);
                            }
                        }

                        $record->update($updateData);

                        return $record;
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

    protected function getPreviewType(Evidence $record): FilePreviewType
    {
        $previewType = FilePreviewType::fromMimeType($record->mime_type);

        if ($previewType !== FilePreviewType::Other) {
            return $previewType;
        }

        $extension = strtolower(pathinfo($record->file_path, PATHINFO_EXTENSION));

        $inferredMime = match ($extension) {
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => 'image/'.$extension,
            'pdf' => 'application/pdf',
            'mp4', 'webm', 'mov', 'avi' => 'video/'.$extension,
            'mp3', 'wav', 'ogg', 'm4a' => 'audio/'.$extension,
            default => null,
        };

        return FilePreviewType::fromMimeType($inferredMime);
    }
}
