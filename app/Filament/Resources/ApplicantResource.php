<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource\Pages;
use App\Filament\Resources\ApplicantResource\RelationManagers;
use App\Models\Applicant;
use App\Models\Question;
use App\Services\ApplicantActions;
use App\Services\WhatsappApiNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontFamily;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantResource extends Resource
{
    protected static ?string $model = Applicant::class;

    protected static ?string $modelLabel = 'Aplicante';
    protected static ?string $pluralModelLabel = 'Aplicantes';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos Personales')
                    ->description('Información de identificación del aplicante.')
                    ->icon('heroicon-m-identification')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('applicant_name')
                            ->label('Nombre Completo')
                            ->required()
                            ->prefixIcon('heroicon-m-user')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('curp')
                            ->label('CURP')
                            ->prefixIcon('heroicon-m-finger-print')
                            ->maxLength(18)
                            ->formatStateUsing(fn(?string $state) => strtoupper($state))
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->validationMessages([
                                'unique' => 'Este CURP ya existe. Por favor verifica el registro.',
                            ]),

                        Forms\Components\TextInput::make('chat_id')
                            ->label('Número de Telefono')
                            ->required()
                            ->tel()
                            ->prefixIcon('heroicon-m-phone')
                            ->formatStateUsing(function ($state, string $operation) {
                                if (!$state) return '-';
                                if ($operation !== 'view') return $state;

                                return str_starts_with($state, '521') ? substr($state, 3) : $state;
                            }),

                        Forms\Components\Select::make('gender')
                            ->label('Género')
                            ->required()
                            ->options([
                                'man' => 'Hombre',
                                'woman' => 'Mujer',
                            ])
                            ->native(false),
                    ]),

                Forms\Components\Section::make('Estado del Proceso')
                    ->description('Gestión del grupo y estatus actual.')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->columns(2)
                    ->schema([
                        Forms\Components\ToggleButtons::make('process_status')
                            ->label('Estatus')
                            ->options([
                                'in_progress' => 'En Progreso',
                                'approved' => 'Aprobado',
                                'staff_approved' => 'Aprobado por Staff',
                                'rejected' => 'Rechazado',
                                'staff_rejected' => 'Rechazado por Staff',
                                'requires_revision' => 'Requiere Revisión',
                                'canceled' => 'Cancelado',
                            ])
                            ->icons([
                                'in_progress' => 'heroicon-m-arrow-path',
                                'approved' => 'heroicon-m-sparkles',
                                'staff_approved' => 'heroicon-m-check-badge',
                                'rejected' => 'heroicon-m-x-circle',
                                'staff_rejected' => 'heroicon-m-no-symbol',
                                'requires_revision' => 'heroicon-m-exclamation-triangle',
                                'canceled' => 'heroicon-m-x-mark',
                            ])
                            ->colors([
                                'in_progress' => 'info',
                                'approved' => 'success',
                                'staff_approved' => 'success',
                                'rejected' => 'danger',
                                'staff_rejected' => 'danger',
                                'requires_revision' => 'warning',
                                'canceled' => 'gray',
                            ])
                            ->inline()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('group_id')
                            ->relationship('group', 'name')
                            ->label('Grupo Asignado')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-m-user-group')
                            ->disabled(fn(Get $get) => !in_array($get('process_status'), ['approved', 'staff_approved']))
                            ->helperText(fn(Get $get) => in_array($get('process_status'), ['approved', 'staff_approved']) ? 'Solo aplicantes aprobados pueden tener grupo.' : null),
                    ]),

                Forms\Components\Section::make('Seguimiento')
                    ->description('Control de la etapa actual del bot.')
                    ->columns(1)
                    ->schema([
                        Forms\Components\Select::make('current_stage_id')
                            ->relationship('currentStage', 'name')
                            ->required()
                            ->label('Etapa Actual')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('current_question_id', null)),

                        Forms\Components\Select::make('current_question_id')
                            ->label('Pregunta Actual')
                            ->required()
                            ->native(false)
                            ->options(function (Get $get) {
                                $stageId = $get('current_stage_id');
                                if (!$stageId) return [];
                                return Question::where('stage_id', $stageId)->pluck('question_text', 'id');
                            }),
                    ]),

                Forms\Components\Section::make('Detalles de Rechazo')
                    ->icon('heroicon-m-x-circle')
                    ->hidden(fn(Get $get) => !in_array($get('process_status'), ['rejected', 'staff_rejected']))
                    ->schema([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Motivo')
                            ->formatStateUsing(function (?string $state) {
                                $reasons = [
                                    'no_children' => 'No tiene hijos',
                                    'contract_issues' => 'Problemas con el contrato',
                                    'not_owner' => 'No es dueño del terreno',
                                    'lives_too_far' => 'Vive muy lejos del terreno',
                                    'less_than_a_year' => 'Tiene menos de un año con el terreno',
                                    'late_payments' => 'Atrasado con los pagos',
                                    'out_of_coverage' => 'Vive en una colonia no atendida o de riesgo',
                                ];

                                return $reasons[$state] ?? $state;
                            })
                            ->columnSpanFull()
                            ->autoSize()
                            ->disabled(),
                    ]),

                Forms\Components\Actions::make([
                    // Botón para aprobar una etapa y pasar a la siguiente
                    Action::make('approveStage')
                        ->label("Aprobar etapa")
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Pasar a la siguiente etapa')
                        ->modalDescription("¿Estás seguro de aprobar a este aplicante? Esta acción no se puede deshacer.\nRecuerda que si han pasado 24 horas desde la última interacción del aplicante con el bot se cobrara este mensaje")
                        ->modalSubmitActionLabel('Sí, aprobar!')
                        ->action(fn(Applicant $record) => ApplicantActions::approveStage($record)),


                    // Botón para aprobar al aplicante de forma definitiva
                    Action::make('approveFinal')
                        ->label("Aprobar definitivamente")
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aprobar aplicante')
                        ->modalDescription("Esta acción marcará al aplicante como aprobado y le enviará el enlace para la selección de grupo. ¿Estás seguro?\nRecuerda que si han pasado 24 horas desde la última interacción del aplicante con el bot se cobrara este mensaje")
                        ->action(fn(Applicant $record) => ApplicantActions::approveApplicantFinal($record)),

                    // Botón de mensaje personalizado
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
                        ->disabled(function (Applicant $applicant) {
                            $conversation = $applicant->conversation;
                            if (! $conversation) return true;

                            $last = $conversation->messages()->where('role', 'user')->latest('created_at')->first();
                            if (! $last) return true;

                            return $last->created_at->lt(now()->subHours(23));
                        })
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
                        ->modalDescription("¿Estás seguro de reenviar la pregunta actual a este aplicante?\nRecuerda que si han pasado 24 horas desde la última interacción del aplicante con el bot se cobrara este mensaje")
                        ->action(fn(Applicant $record) => ApplicantActions::reSendCurrentQuestion($record)),

                    // Botón para reenviar el enlace de selección de grupo
                    Action::make('resendGroupLink')
                        ->label("Reenviar enlace de grupo")
                        ->icon('heroicon-o-link')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reenviar enlace de grupo')
                        ->modalDescription("¿Estás seguro de reenviar el enlace de selección de grupo a este aplicante?\nRecuerda que si han pasado 24 horas desde la última interacción del aplicante con el bot se cobrara este mensaje")
                        ->action(fn(Applicant $record) => ApplicantActions::reSendGroupSelectionLink($record)),

                    // Botón para reiniciar el proceso del aplicante
                    Action::make('restartApplicant')
                        ->label("Reiniciar")
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reiniciar proceso del aplicante')
                        ->modalDescription("¿Estás seguro de reiniciar el proceso de este aplicante? Se eliminarán todas las respuestas existentes.\nRecuerda que si han pasado 24 horas desde la última interacción del aplicante con el bot se cobrara este mensaje")
                        ->action(fn(Applicant $record) => ApplicantActions::resetApplicant($record)),

                    // Botón para rechazar al aplicante
                    Action::make('rejectApplicant')
                        ->label('Rechazar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Select::make('predefined_reason')
                                ->label('Motivo de rechazo')
                                ->options([
                                    'no_children' => 'No tiene hijos',
                                    'contract_issues' => 'Problemas con el contrato',
                                    'not_owner' => 'No es dueño del terreno',
                                    'lives_too_far' => 'Vive muy lejos del terreno',
                                    'less_than_a_year' => 'Tiene menos de un año con el terreno',
                                    'late_payments' => 'Atrasado con los pagos',
                                    'out_of_coverage' => 'Vive en una colonia no atendida o de riesgo',
                                    'other' => 'Otro (Especificar)',
                                ])
                                ->required()
                                ->live(),

                            Forms\Components\Textarea::make('reason')->label('Especificar razón')->required(fn(Get $get) => $get('predefined_reason') === 'other')->visible(fn(Get $get) => $get('predefined_reason') === 'other')->rows(3)->placeholder('Escribe la razón detallada...'),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading('Rechazar al aplicante')
                        ->modalDescription("¿Estás seguro de rechazar a este aplicante?\nRecuerda que si han pasado 24 horas desde la última interacción del aplicante con el bot se cobrara este mensaje")
                        ->action(function (array $data, Applicant $record) {
                                ApplicantActions::rejectApplicant($record, $data['predefined_reason'] === 'other' ? $data['reason'] : $data['predefined_reason']);
                            }),
                ])
                    ->fullWidth()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('applicant_name')
                    ->label('Nombre')
                    ->searchable()
                    ->description(function (Applicant $record): ?string {
                        if (in_array($record->process_status, ['approved', 'staff_approved'])) {
                            if ($record->group) {
                                return $record->group->name;
                            }

                            return 'Sin grupo asignado';
                        }
                        return null;
                    }),

                TextColumn::make('chat_id')
                    ->label('Número de Telefono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';

                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn($state) => 'https://wa.me/' . $state)
                    ->openUrlInNewTab(),

                TextColumn::make('curp')
                    ->label('CURP')
                    ->fontFamily(FontFamily::Mono)
                    ->formatStateUsing(fn(string $state) => strtoupper($state))
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('currentStage.name')
                    ->label('Etapa')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('process_status')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in_progress' => 'En Progreso',
                        'approved' => 'IA: Aprobado',
                        'rejected' => 'IA: Rechazado',
                        'staff_approved' => 'Staff: Aprobado',
                        'staff_rejected' => 'Staff: Rechazado',
                        'requires_revision' => 'Revisión',
                        'canceled' => 'Cancelado',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'approved' => 'success',
                        'staff_approved' => 'success',
                        'rejected' => 'danger',
                        'staff_rejected' => 'danger',
                        'requires_revision' => 'warning',
                        'canceled' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'in_progress' => 'heroicon-m-arrow-path',
                        'approved' => 'heroicon-m-sparkles',
                        'staff_approved' => 'heroicon-m-check-badge',
                        'rejected' => 'heroicon-m-x-circle',
                        'staff_rejected' => 'heroicon-m-no-symbol',
                        'requires_revision' => 'heroicon-m-exclamation-triangle',
                        'canceled' => 'heroicon-m-x-mark',
                        default => 'heroicon-m-minus',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('process_status')
                    ->label('Filtrar por Estatus')
                    ->options([
                        'in_progress' => 'En Progreso',
                        'approved' => 'Aprobado',
                        "staff_approved" => "Aprobado por Staff",
                        'rejected' => 'Rechazado',
                        "staff_rejected" => "Rechazado por Staff",
                        'requires_revision' => 'Requiere Revisión',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('gray'),
                    Tables\Actions\EditAction::make()->color('primary'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->conversation) {
                                    $record->conversation->delete();
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('exportSelected')
                        ->label('Exportar a CSV')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->action(function (Collection $records) {
                            return new StreamedResponse(function () use ($records) {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['Telefono', 'Nombre', 'CURP', 'Estatus']);

                                $records->each(function ($applicant) use ($handle) {
                                    fputcsv($handle, [
                                        $applicant->chat_id,
                                        $applicant->applicant_name,
                                        $applicant->curp,
                                        $applicant->process_status,
                                    ]);
                                });

                                fclose($handle);
                            }, 200, [
                                'Content-Type' => 'text/csv',
                                'Content-Disposition' => 'attachment; filename="aplicantes-export-' . now()->format('Y-m-d') . '.csv"',
                            ]);
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ApplicantQuestionResponseRelationManager::class,
            RelationManagers\ConversationRelationManager::class,
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
