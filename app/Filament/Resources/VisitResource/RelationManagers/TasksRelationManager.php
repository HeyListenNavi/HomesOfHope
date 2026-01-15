<?php

namespace App\Filament\Resources\VisitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tareas';

    protected static ?string $icon = 'heroicon-o-clipboard-document-check';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalle de la Tarea')
                    ->description('Acciones a realizar post-visita.')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->placeholder('Ej: Gestionar cita médica'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Instrucciones')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Planificación')
                    ->columns(2)
                    ->schema([
                        // Prioridad y Estado
                        Forms\Components\Select::make('priority')
                            ->label('Prioridad')
                            ->options([
                                'low' => 'Baja',
                                'medium' => 'Media',
                                'high' => 'Alta',
                                'critical' => 'Crítica',
                            ])
                            ->default('medium')
                            ->required()
                            ->prefixIcon('heroicon-o-flag'),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'in_progress' => 'En Progreso',
                                'completed' => 'Completada',
                                'cancelled' => 'Cancelada',
                            ])
                            ->default('pending')
                            ->required()
                            ->prefixIcon('heroicon-o-arrow-path'),

                        // Asignación
                        Forms\Components\Select::make('assigned_to')
                            ->label('Responsable')
                            ->relationship('assignee', 'name') // Asegúrate que la relación assignee exista en Task
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-o-user'),

                        Forms\Components\DateTimePicker::make('due_date')
                            ->label('Vencimiento')
                            ->native(false)
                            ->prefixIcon('heroicon-o-calendar'),
                            
                        // Campos ocultos automáticos
                        Forms\Components\Hidden::make('assigned_by')
                            ->default(Auth::id()),
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
                    ->description(fn ($record) => \Illuminate\Support\Str::limit($record->description, 30)),

                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Responsable')
                    ->icon('heroicon-o-user-circle')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vence')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->slideOver(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}