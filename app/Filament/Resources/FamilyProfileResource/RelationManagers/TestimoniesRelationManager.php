<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TestimoniesRelationManager extends RelationManager
{
    protected static string $relationship = 'testimonies';

    protected static ?string $title = 'Testimonios';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del testimonio')
                    ->schema([
                        Forms\Components\Grid::make(12)
                            ->schema([

                                Forms\Components\Select::make('language')
                                    ->label('Idioma')
                                    ->options([
                                        'es' => 'Español',
                                        'en' => 'Inglés',
                                        'pt' => 'Portugués',
                                    ])
                                    ->required()
                                    ->columnSpan(4),

                                Forms\Components\Select::make('recorded_by')
                                    ->label('Grabado por')
                                    ->relationship('recorder', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(4),

                                Forms\Components\DateTimePicker::make('recorded_at')
                                    ->label('Fecha de grabación')
                                    ->required()
                                    ->columnSpan(4),
                            ]),
                    ]),
                Forms\Components\Section::make('Audio del testimonio')
                    ->schema([
                        Forms\Components\FileUpload::make('audio_path')
                            ->label('Archivo de audio')
                            ->disk('public')
                            ->directory('testimonies/audio')
                            ->acceptedFileTypes([
                                'audio/mpeg',
                                'audio/mp3',
                                'audio/wav',
                                'audio/ogg',
                            ])
                            ->maxSize(10240) // 10 MB
                            ->columnSpanFull()
                            ->helperText('Formatos permitidos: MP3, WAV, OGG'),
                    ]),
                Forms\Components\Section::make('Contenido')
                    ->schema([
                        Forms\Components\Textarea::make('summary')
                            ->label('Resumen')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('transcription')
                            ->label('Transcripción completa')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('recorded_at')
            ->columns([

                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('language')
                    ->label('Idioma')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'es' => 'ES',
                        'en' => 'EN',
                        'pt' => 'PT',
                        default => strtoupper($state),
                    }),

                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Grabado por')
                    ->searchable(),

                Tables\Columns\TextColumn::make('summary')
                    ->label('Resumen')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('audio_path')
                    ->label('Audio')
                    ->boolean()
                    ->trueIcon('heroicon-o-speaker-wave')
                    ->falseIcon('heroicon-o-x-mark')
                    ->tooltip(fn ($record) => $record->audio_path
                        ? 'Audio disponible'
                        : 'Sin audio'
                    ),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo testimonio'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
