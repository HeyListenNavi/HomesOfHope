<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;

class TestimoniesRelationManager extends RelationManager
{
    protected static string $relationship = 'testimonies';

    protected static ?string $title = 'Testimonios';

    protected static ?string $icon = 'heroicon-s-microphone';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // COLUMNA IZQUIERDA: CONTENIDO (2/3)
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 2])
                            ->schema([
                                Forms\Components\Section::make('Contenido del Testimonio')
                                    ->description('Detalles narrativos y archivos multimedia.')
                                    ->icon('heroicon-s-chat-bubble-bottom-center-text')
                                    ->schema([
                                        // CAMBIO DE ETIQUETA AQUÍ
                                        Forms\Components\Textarea::make('summary')
                                            ->label('Resumen de la Historia')
                                            ->placeholder('Breve descripción del impacto o historia...')
                                            ->rows(3)
                                            ->autosize()
                                            ->required()
                                            ->columnSpanFull(),

                                        Forms\Components\FileUpload::make('audio_path')
                                            ->label('Archivo de Audio')
                                            ->disk('public')
                                            ->directory('testimonies/audio')
                                            ->acceptedFileTypes(['audio/*'])
                                            ->maxSize(51200) // 50MB
                                            ->previewable()
                                            ->downloadable()
                                            ->openable()
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('transcription')
                                            ->label('Transcripción Completa')
                                            ->placeholder('Texto completo si está disponible...')
                                            ->rows(5)
                                            ->autosize()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // COLUMNA DERECHA: INFORMACIÓN DE REGISTRO (1/3)
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                // CAMBIO DE TÍTULO DE SECCIÓN AQUÍ
                                Forms\Components\Section::make('Detalles del Registro')
                                    ->icon('heroicon-s-information-circle')
                                    ->schema([
                                        Forms\Components\Select::make('language')
                                            ->label('Idioma')
                                            ->options([
                                                'es' => 'Español',
                                                'en' => 'Inglés',
                                            ])
                                            ->default('es')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->native(false)
                                            ->prefixIcon('heroicon-s-language'),

                                        Forms\Components\DateTimePicker::make('recorded_at')
                                            ->label('Fecha de Grabación')
                                            ->native(false)
                                            ->default(now())
                                            ->prefixIcon('heroicon-s-calendar')
                                            ->required(),

                                        Forms\Components\Select::make('recorded_by')
                                            ->label('Grabado por')
                                            ->relationship('recorder', 'name')
                                            ->default(Auth::id())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->prefixIcon('heroicon-s-microphone'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Fecha')
                    ->date('d M Y')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-s-calendar')
                    ->description(fn ($record) => $record->recorded_at->diffForHumans()),

                Tables\Columns\TextColumn::make('language')
                    ->label('Idioma')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),

                Tables\Columns\TextColumn::make('summary')
                    ->label('Resumen')
                    ->limit(50)
                    ->icon('heroicon-s-document-text')
                    ->color('gray')
                    ->wrap(),

                Tables\Columns\IconColumn::make('audio_path')
                    ->label('Audio')
                    ->icon(fn ($state) => $state ? 'heroicon-s-play-circle' : 'heroicon-s-minus')
                    ->color(fn ($state) => $state ? 'primary' : 'gray')
                    ->tooltip(fn ($state) => $state ? 'Reproducible en detalles' : 'No disponible'),

                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Grabado por')
                    ->icon('heroicon-s-user')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Testimonio')
                    ->icon('heroicon-s-plus')
                    ->slideOver()
                    ->modalWidth('4xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->slideOver()
                    ->modalWidth('4xl'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('recorded_at', 'desc');
    }
}
