<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource\Pages;
use App\Filament\Resources\ApplicantResource\RelationManagers;
use App\Models\Applicant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicantResource extends Resource
{
    protected static ?string $model = Applicant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('curp')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_approved')
                    ->required(),
                Forms\Components\TextInput::make('rejection_reason')
                    ->maxLength(255)
                    ->hidden(fn (Forms\Get $get) => $get('is_approved')), // Se oculta si está aprobado
                Forms\Components\Select::make('group_id')
                    ->relationship('group', 'name')
                    ->hidden(fn (Forms\Get $get) => !$get('is_approved'))
                    ->nullable(),
                Forms\Components\KeyValue::make('final_evaluation_data')
                    ->columnSpanFull()
                    ->disabled(), // Los datos de evaluación no se editan desde aquí
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Información del Solicitante')
                    ->columns(2)
                    ->schema([
                        Components\TextEntry::make('curp'),
                        Components\TextEntry::make('group.name')
                            ->label('Grupo Asignado')
                            ->badge()
                            ->color('success')
                            ->default('N/A')
                            ->visible(fn (Applicant $record) => $record->is_approved),
                        Components\IconEntry::make('is_approved')
                            ->label('Estado')
                            ->boolean()
                            ->icon(fn (bool $state): string => match ($state) {
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-x-circle',
                            })
                            ->color(fn (bool $state): string => match ($state) {
                                true => 'success',
                                false => 'danger',
                            }),
                        Components\TextEntry::make('rejection_reason')
                            ->label('Motivo de Rechazo')
                            ->visible(fn (Applicant $record) => !$record->is_approved),
                    ]),
                Components\Section::make('Datos de Evaluación')
                    ->schema([
                        Components\KeyValueEntry::make('final_evaluation_data')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('curp')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Grupo Asignado')
                    ->badge()
                    ->default('N/A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Aprobados/Rechazados')
                    ->nullable()
                    ->trueLabel('Aprobados')
                    ->falseLabel('Rechazados')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
