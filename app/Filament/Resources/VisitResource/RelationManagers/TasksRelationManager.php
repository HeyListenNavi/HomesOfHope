<?php

namespace App\Filament\Resources\VisitResource\RelationManagers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Filament\Forms;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tareas y Seguimiento';

    protected static ?string $icon = 'heroicon-s-clipboard-document-check';

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
                                Forms\Components\Section::make('Detalle de la Tarea')
                                    ->description('Acciones concretas a realizar.')
                                    ->icon('heroicon-s-pencil-square')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título de la Tarea')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Ej: Gestionar cita médica, conseguir útiles...')
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('description')
                                            ->label('Instrucciones / Notas')
                                            ->rows(4)
                                            ->placeholder('Detalles adicionales para completar la tarea.')
                                            ->autosize()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // COLUMNA DERECHA: PLANIFICACIÓN (1/3)
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Section::make('Prioridad y Estado')
                                    ->icon('heroicon-s-flag')
                                    ->schema([
                                        // ToggleButtons para Prioridad (Visualmente impactante)
                                        ToggleButtons::make('priority')
                                            ->label('Prioridad')
                                            ->options(TaskPriority::class)
                                            ->default(TaskPriority::Medium)
                                            ->required(),

                                        Forms\Components\Select::make('status')
                                            ->label('Estado Actual')
                                            ->options(TaskStatus::class)
                                            ->default(TaskStatus::Pending)
                                            ->native(false)
                                            ->required()
                                            ->prefixIcon('heroicon-s-arrow-path'),

                                        Forms\Components\DatePicker::make('due_date')
                                            ->label('Fecha Vencimiento')
                                            ->native(false)
                                            ->prefixIcon('heroicon-s-calendar'),

                                        Forms\Components\Select::make('assigned_to')
                                            ->label('Asignado a')
                                            ->relationship('assignee', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(Auth::id())
                                            ->required()
                                            ->prefixIcon('heroicon-s-user'),

                                        Forms\Components\Hidden::make('assigned_by')
                                            ->default(Auth::id()),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tarea')
                    ->searchable()
                    ->description(fn ($record) => Str::limit($record->description, 40))
                    ->wrap(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),

                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Responsable')
                    ->icon('heroicon-s-user-circle')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vence')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->due_date && $record->due_date->isPast() && $record->status !== TaskStatus::Completed ? 'danger' : 'gray'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva Tarea')
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
            ->defaultSort('created_at', 'desc');
    }
}
