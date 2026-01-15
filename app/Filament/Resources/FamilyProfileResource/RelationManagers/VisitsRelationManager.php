<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    protected static ?string $title = 'Visitas'; // Actualicé el título

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECCIÓN 1: DATOS DE LA VISITA (Tu código existente) ---
                Forms\Components\Section::make('Información de la Visita')
                    ->columns(3) // Optimizamos espacio
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'scheduled' => 'Programada',
                                'completed' => 'Completada',
                                'cancelled' => 'Cancelada',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('location_type')
                            ->label('Ubicación')
                            ->options([
                                'home' => 'Domicilio',
                                'office' => 'Oficina',
                                'remote' => 'Remota',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('attended_by')
                            ->label('Atendida por')
                            ->relationship('attendant', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Programada')
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Finalizada')
                            ->native(false),
                            
                        Forms\Components\Textarea::make('outcome_summary')
                            ->label('Resumen')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                // --- SECCIÓN 2: TAREAS (EL REPEATER MÁGICO) ---
                Forms\Components\Section::make('Tareas Derivadas')
                    ->description('Agrega tareas rápidas relacionadas a esta visita.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Repeater::make('tasks') // Nombre de la relación en el modelo Visit
                            ->relationship() // <--- ESTO ES LA CLAVE. Conecta con HasMany
                            ->label('Lista de Tareas')
                            ->addActionLabel('Agregar Tarea')
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null) // Pone el título en la barra colapsable
                            ->collapsed(fn ($state) => count($state) > 1) // Colapsar items si hay muchos para ahorrar espacio
                            ->columns(4) // Grid de 4 columnas dentro de cada item
                            ->schema([
                                // Fila 1: Título y Asignación
                                Forms\Components\TextInput::make('title')
                                    ->label('Título de la Tarea')
                                    ->required()
                                    ->placeholder('Ej: Comprar medicina')
                                    ->columnSpan(2), // Ocupa mitad

                                Forms\Components\Select::make('assigned_to')
                                    ->label('Responsable')
                                    ->relationship('assignee', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('priority')
                                    ->label('Prioridad')
                                    ->options([
                                        'low' => 'Baja',
                                        'medium' => 'Media',
                                        'high' => 'Alta',
                                        'critical' => 'Urgente',
                                    ])
                                    ->default('medium')
                                    ->prefixIcon('heroicon-o-flag')
                                    ->columnSpan(1),

                                // Fila 2: Detalles extra (Ocupan todo el ancho)
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'pending' => 'Pendiente',
                                                'in_progress' => 'En Curso',
                                                'completed' => 'Lista',
                                            ])
                                            ->default('pending'),

                                        Forms\Components\DatePicker::make('due_date')
                                            ->label('Vencimiento')
                                            ->native(false),

                                        Forms\Components\Textarea::make('description')
                                            ->label('Notas')
                                            ->rows(1)
                                            ->columnSpan(2)
                                            ->placeholder('Detalles opcionales...'),
                                    ]),

                                // Campo oculto automático
                                Forms\Components\Hidden::make('assigned_by')
                                    ->default(Auth::id()),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('scheduled_at')
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors(['success' => 'completed', 'warning' => 'scheduled', 'danger' => 'cancelled']),
                
                // Columna visual para ver cuántas tareas tiene esa visita
                Tables\Columns\TextColumn::make('tasks_count')
                    ->counts('tasks')
                    ->label('Tareas')
                    ->badge()
                    ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva Visita')
                    ->slideOver()
                    ->modalWidth('5xl'), // Hacemos el modal ancho al crear
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    //->slideOver() // Panel lateral en lugar de modal central (mejor para listas largas)
                    ->modalWidth('5xl') // ANCHO EXTRA para que el repeater quepa bien
                    ->modalHeading('Editar Visita y Tareas'), 

                Tables\Actions\DeleteAction::make(),
            ]);
    }
}