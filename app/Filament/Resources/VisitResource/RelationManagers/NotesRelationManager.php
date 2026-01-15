<?php

namespace App\Filament\Resources\VisitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Notas';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Nueva Nota')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'link'])
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_private') // Recuerda agregar 'is_private' al $fillable de Note
                            ->label('Nota Privada')
                            ->onIcon('heroicon-o-lock-closed')
                            ->offIcon('heroicon-o-eye')
                            ->default(false),
                            
                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Autor')
                    ->icon('heroicon-o-user')
                    ->sortable(),

                Tables\Columns\TextColumn::make('content')
                    ->label('Nota')
                    ->html()
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_private')
                    ->label('Privado')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-globe-alt'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->slideOver(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}