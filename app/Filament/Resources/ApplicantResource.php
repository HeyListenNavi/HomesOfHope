<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource\Pages;
use App\Filament\Resources\ApplicantResource\RelationManagers\ApplicantConversationRelationManager;
use App\Filament\Resources\ApplicantResource\RelationManagers\ApplicantQuestionResponseRelationManager;
use App\Models\Applicant;
use App\Models\Question;
use App\Services\ApplicantActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;



class ApplicantResource extends Resource
{
    protected static ?string $model = Applicant::class;

    protected static ?string $modelLabel = 'Aplicante';

    protected static ?string $pluralModelLabel = 'Aplicantes';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos Personales')
                    ->description('Completa la información básica del aplicante.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('applicant_name')->required()->label('Nombre Completo'),
                        Forms\Components\TextInput::make('curp')->required()->label('CURP'),
                        Forms\Components\TextInput::make('chat_id')->required()->label('Número de Teléfono'),
                        Forms\Components\Select::make('gender')->required()->label('Genero')->options([
                            'man' => 'Hombre',
                            'woman' => 'Mujer',
                        ]),
                    ]),
                Forms\Components\Section::make('Grupo y Proceso')
                    ->description('Selecciona el grupo y el estado actual del proceso.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('process_status')->options([
                            'in_progress' => 'En Progreso',
                            'approved' => 'Aprobado',
                            'rejected' => 'Rechazado',
                            "requires_revision" => "Requiere Revisión",
                            "canceled" => "Cancelado",
                        ])
                            ->required()
                            ->label('Estado del Proceso')
                            ->live()
                            ->native(false),
                        Forms\Components\Select::make('group_id')
                            ->relationship('group', 'name')
                            ->label('Grupo')
                            ->disabled(function (Get $get) {
                                return $get('process_status') != 'approved';
                            }),
                    ]),
                Forms\Components\Section::make('Etapa y Pregunta Actual')
                    ->description('Indica la etapa y la pregunta en la que se encuentra el aplicante.')
                    ->schema([
                        Forms\Components\Select::make('current_stage_id')
                            ->relationship('currentStage', 'name')
                            ->required()
                            ->label('Etapa Actual')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('current_question_id', null);
                            }),
                        Forms\Components\Select::make('current_question_id')
                            ->label('Pregunta Actual')
                            ->required()
                            ->native(false)
                            ->options(function (Get $get) {
                                $stageId = $get('current_stage_id');
                                if (!$stageId) {
                                    return [];
                                }
                                return Question::where('stage_id', $stageId)
                                    ->pluck('question_text', 'id')
                                    ->toArray();
                            })
                    ]),
                Forms\Components\Section::make('Motivo de Descalificación')
                    ->description('La razón por la que el aplicante ha sido rechazado.')
                    ->hidden(function (Get $get) {
                        $status = $get('process_status');

                        if ($status == 'rejected') {
                            return false;
                        }

                        if ($status == 'requires_revision') {
                            return false;
                        }

                        return true;
                    })
                    ->schema([
                        Forms\Components\Textarea::make('rejection_reason')->nullable()->label('Razón de Descalificación')->columnSpanFull()->rows(10)->autosize(),
                    ]),
                Actions::make([
                    // Botón para aprobar una etapa y pasar a la siguiente
                    Action::make('approveStage')
                        ->label("Aprobar etapa")
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Pasar a la siguiente etapa')
                        ->modalDescription("¿Estás seguro de aprobar a este aplicante? Esta acción no se puede deshacer.")
                        ->modalSubmitActionLabel('Sí, aprobar!')
                        ->action(fn(Applicant $record) => ApplicantActions::approveStage($record)),

                    // Botón para aprobar al aplicante de forma definitiva
                    Action::make('approveFinal')
                        ->label("Aprobar definitivamente")
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aprobar aplicante')
                        ->modalDescription("Esta acción marcará al aplicante como aprobado y le enviará el enlace para la selección de grupo. ¿Estás seguro?")
                        ->action(fn(Applicant $record) => ApplicantActions::approveApplicantFinal($record)),

                    // --- Botón de mensaje personalizado 
                    Action::make('sendCustomMessage')
                        ->label("Enviar mensaje personalizado")
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->form([
                            Forms\Components\Textarea::make('message')
                                ->label('Mensaje')
                                ->required()
                                ->rows(5)
                                ->placeholder('Escribe tu mensaje aquí...'),
                        ])
                        ->modalHeading('Enviar mensaje personalizado')
                        ->action(function (array $data, Applicant $record) {
                            ApplicantActions::sendCustomMessage($record, $data['message']);
                        }),

                    // Botón para reenviar la pregunta actual
                    Action::make('resendQuestion')
                        ->label("Reenviar pregunta actual")
                        ->icon('heroicon-o-question-mark-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reenviar pregunta')
                        ->modalDescription('¿Estás seguro de reenviar la pregunta actual a este aplicante?')
                        ->action(fn(Applicant $record) => ApplicantActions::reSendCurrentQuestion($record)),

                    // Botón para reenviar el enlace de selección de grupo
                    Action::make('resendGroupLink')
                        ->label("Reenviar enlace de grupo")
                        ->icon('heroicon-o-link')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reenviar enlace de grupo')
                        ->modalDescription('¿Estás seguro de reenviar el enlace de selección de grupo a este aplicante?')
                        ->action(fn(Applicant $record) => ApplicantActions::reSendGroupSelectionLink($record)),

                    // Botón para reiniciar el proceso del aplicante
                    Action::make('restartApplicant')
                        ->label("Reiniciar")
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reiniciar proceso del aplicante')
                        ->modalDescription("¿Estás seguro de reiniciar el proceso de este aplicante? Se eliminarán todas las respuestas existentes.")
                        ->action(fn(Applicant $record) => ApplicantActions::resetApplicant($record)),
                ])
                    ->fullWidth()
                    ->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('applicant_name')->label('Nombre')->searchable(),
                TextColumn::make('chat_id')->label('Número de Teléfono')->searchable(),
                TextColumn::make('currentStage.name')->label('Etapa Actual'),
                Tables\Columns\SelectColumn::make('process_status')->options([
                    'in_progress' => 'En Progreso',
                    'approved' => 'Aprobado',
                    'rejected' => 'Rechazado',
                    "requires_revision" => "Requiere Revision",
                    "canceled" => "Cancelado",
                ])->label('Estado del Proceso'),
                IconColumn::make('process_status')
                    ->label('Estado')
                    ->icon(fn(string $state): string => match ($state) {
                        'in_progress' => 'heroicon-o-arrow-path',
                        'approved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        'requires_revision' => 'heroicon-o-exclamation-triangle',
                        "canceled" => "lucide-ban",
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'requires_revision' => 'warning',
                        "canceled" => "gray",
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ApplicantQuestionResponseRelationManager::class,
            ApplicantConversationRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplicants::route('/'),
            'create' => Pages\CreateApplicant::route('/create'),
            'view' => Pages\ViewApplicant::route('/{record}'),
            'edit' => Pages\EditApplicant::route('/{record}/edit'),
        ];
    }
}
