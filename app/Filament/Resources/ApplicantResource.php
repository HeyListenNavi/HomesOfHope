<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource\Pages;
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

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('chat_id')->required()->readOnly(),
                Forms\Components\Select::make('current_stage_id')->relationship('currentStage', 'name')->required(),
                Forms\Components\Select::make('current_question_id')->relationship('currentQuestion', 'question_text')->required(),
                Forms\Components\Select::make('process_status')->options([
                    'in_progress' => 'En Progreso',
                    'completed' => 'Completado',
                    'rejected' => 'Rechazado',
                    'approved' => 'Aprobado',
                ])->required(),
                Forms\Components\Toggle::make('is_approved')->label('¿Aprobado?')->nullable(),
                Forms\Components\Textarea::make('rejection_reason')->nullable(),
                Forms\Components\KeyValue::make('evaluation_data')->label('Respuestas del Solicitante'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('chat_id'),
                TextColumn::make('currentStage.name'),
                TextColumn::make('process_status'),
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
            //
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