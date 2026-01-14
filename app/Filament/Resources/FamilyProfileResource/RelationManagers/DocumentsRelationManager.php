<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $recordTitleAttribute = 'original_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Archivo')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Subir archivo')
                            ->required()
                            ->disk('public')
                            ->directory('documents')
                            ->getUploadedFileNameForStorageUsing(fn ($file) => $file->getClientOriginalName()),
                        Forms\Components\TextInput::make('original_name')
                            ->label('Nombre original')
                            ->disabled(),
                        Forms\Components\TextInput::make('mime_type')->disabled(),
                        Forms\Components\TextInput::make('size')->disabled(),
                    ]),

                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->required()
                            ->options([
                                'report' => 'Reporte',
                                'contract' => 'Contrato',
                                'photo' => 'Foto',
                                'other' => 'Otro',
                            ]),
                        Forms\Components\Select::make('uploaded_by')
                            ->relationship('uploader', 'name')
                            ->label('Subido por')
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('original_name')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('document_type')->label('Tipo'),
                Tables\Columns\TextColumn::make('size')->label('Tamaño')->formatStateUsing(fn ($state) => round($state / 1024, 2) . ' KB'),
                Tables\Columns\TextColumn::make('uploaded_by.name')->label('Subido por'),
                Tables\Columns\TextColumn::make('created_at')->label('Subido el')->dateTime('d/m/Y H:i'),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('Archivo')
                    ->url(fn ($record) => $record->url)
                    //->icon('heroicon-o-download'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['file_path'])) {
            $file = $data['file_path'];
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getClientMimeType();
            $data['size'] = $file->getSize();
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->mutateFormDataBeforeCreate($data);
    }
}
