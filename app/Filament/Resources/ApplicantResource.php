<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource\Pages;
use App\Filament\Resources\ApplicantResource\RelationManagers\ApplicantConversationRelationManager;
use App\Filament\Resources\ApplicantResource\RelationManagers\ApplicantQuestionResponseRelationManager;
use App\Models\Applicant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

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
                Forms\Components\Toggle::make('is_approved')->label('¿Aprobado?')->nullable()->columnSpanFull()->inline(false)->onColor('primary')->offColor('danger'),
                Forms\Components\TextInput::make('chat_id')->required()->label('Número de Teléfono'),
                Forms\Components\Select::make('current_stage_id')->relationship('currentStage', 'name')->required()->label('Etapa Actual')->native(false),
                Forms\Components\Select::make('current_question_id')->relationship('currentQuestion', 'question_text')->required()->label('Pregunta')->native(false),
                Forms\Components\Select::make('process_status')->options([
                    'in_progress' => 'En Progreso',
                    'completed' => 'Completado',
                    'rejected' => 'Rechazado',
                    'approved' => 'Aprobado',
                ])->required()->label('Estado del Proceso')->native(false),
                Forms\Components\Textarea::make('rejection_reason')->nullable()->label('Razon de Descalificación')->columnSpanFull()->rows(10)->autosize(),
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
                    'completed' => 'Completado',
                    'rejected' => 'Rechazado',
                    'approved' => 'Aprobado',
                ])->label('Estado del Proceso'),
                Tables\Columns\IconColumn::make('is_approved')->boolean()->label('Aprobado'),
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
