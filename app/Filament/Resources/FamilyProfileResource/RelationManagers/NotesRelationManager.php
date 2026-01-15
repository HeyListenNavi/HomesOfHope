<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Notas';

    protected static ?string $modelLabel = 'Nota';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Usamos un Section para agrupar visualmente y dar elevación
                Forms\Components\Section::make('Detalles de la Nota')
                    ->description('Agrega observaciones o comentarios relevantes.')
                    ->schema([
                        
                        // Campo oculto para asignar automáticamente el autor
                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id())
                            ->required(),

                        // Editor de texto enriquecido para mejor formato
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->columnSpanFull() // Ocupa todo el ancho
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'link',
                                'redo',
                                'undo',
                            ])
                            ->placeholder('Escribe aquí los detalles de la nota...'),

                        // Agrupación para opciones de estado/visibilidad
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_private')
                                    ->label('Nota Privada')
                                    ->helperText('Solo visible para usuarios con permisos elevados.')
                                    ->onIcon('heroicon-m-eye-slash')
                                    ->offIcon('heroicon-m-eye')
                                    ->default(false)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(2), // Estructura interna de la sección
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                // Columna del Autor con Avatar si tu usuario tiene uno, o solo nombre
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Autor')
                    ->icon('heroicon-m-user')
                    ->sortable()
                    ->searchable(),

                // Contenido truncado para no romper la tabla
                Tables\Columns\TextColumn::make('content')
                    ->label('Contenido')
                    ->html() // Necesario porque usamos RichEditor
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        return strip_tags($column->getState());
                    })
                    ->wrap(),

                // Indicador visual de privacidad
                Tables\Columns\IconColumn::make('is_private')
                    ->label('Privada')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-globe-americas')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->sortable(),

                // Fecha de creación con formato amigable
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                // Filtro para ver solo notas privadas o públicas
                Tables\Filters\TernaryFilter::make('is_private')
                    ->label('Privacidad')
                    ->placeholder('Todas las notas')
                    ->trueLabel('Solo Privadas')
                    ->falseLabel('Solo Públicas'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva Nota'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'); // Las notas más nuevas primero
    }
}