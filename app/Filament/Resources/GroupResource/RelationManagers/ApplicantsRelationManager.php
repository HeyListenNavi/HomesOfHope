<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Models\Applicant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicantsRelationManager extends RelationManager
{
    protected static string $relationship = 'applicants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('applicant_id')
                    ->label('Solicitante a asignar')
                    ->options(fn() => Applicant::whereNull('group_id')
                        ->orderBy('applicant_name')
                        ->pluck('applicant_name', 'id')
                        ->toArray())
                    ->searchable()
                    ->required()
                    ->columnSpanFull()
                    ->helperText('Selecciona un solicitante que aún no esté asignado a ningún grupo.'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('applicant_name'),
                TextColumn::make("curp"),
                TextColumn::make('gender')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'man' => 'success',
                        'woman' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'man' => 'Hombre',
                        'woman' => 'Mujer',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
