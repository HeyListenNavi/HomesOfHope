<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Notas';

    protected static ?string $modelLabel = 'Nota';

    protected static ?string $icon = 'heroicon-s-document-text';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles de la Nota')
                    ->description('Agrega observaciones o comentarios relevantes para el seguimiento.')
                    ->icon('heroicon-s-pencil-square')
                    ->schema([

                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id())
                            ->required(),

                        Forms\Components\Textarea::make('content')
                            ->label('Contenido')
                            ->required()
                            ->columnSpanFull()
                            ->rows(5)
                            ->autosize()
                            ->placeholder('Escribe aquí los detalles, acuerdos o incidentes...'),

                        Forms\Components\Toggle::make('is_private')
                            ->label('Nota Confidencial')
                            ->helperText('Solo visible para administradores y el creador.')
                            ->onIcon('heroicon-s-lock-closed')
                            ->offIcon('heroicon-s-globe-americas')
                            ->onColor('danger')
                            ->offColor('success')
                            ->default(false),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y, H:i A')
                    ->sortable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Autor')
                    ->icon('heroicon-s-user')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('content')
                    ->label('Resumen')
                    ->formatStateUsing(fn (string $state) => strip_tags($state))
                    ->limit(60)
                    ->wrap()
                    ->tooltip(fn ($record) => strip_tags($record->content)),
            ])
            ->filters([
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar Nota')
                    ->icon('heroicon-s-plus')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading('Crear Nueva Nota'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading('Editar Nota'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash')
                    ->modalHeading('Borrar Nota'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
