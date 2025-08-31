<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource\Pages;
use App\Filament\Resources\ApplicantResource\RelationManagers\ApplicantConversationRelationManager;
use App\Filament\Resources\ApplicantResource\RelationManagers\ApplicantQuestionResponseRelationManager;
use App\Models\Applicant;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;


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
			Forms\Components\TextInput::make('gender')->required()->label('Genero'),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
