<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Filament\Resources\ApplicantResource;
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
                Tables\Actions\Action::make('assignApplicant')
                    ->label('Asignar aplicante')
                    ->form([
                        Select::make('applicant_id')
                            ->label('Solicitante')
                            ->options(fn() => Applicant::whereNull('group_id')
                                ->whereNotNull('applicant_name')
                                ->orderBy('applicant_name')
                                ->pluck('applicant_name', 'id')
                                ->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $applicant = Applicant::find($data['applicant_id']);
                        if (! $applicant) {
                            return;
                        }

                        $group = $this->getOwnerRecord();

                        $capacity = $group->capacity;
                        $currentCount = $group->applicants()->count();

                        if ($currentCount >= $capacity) {
                            return;
                        }

                        $applicant->update(['group_id' => $group->id]);
                    })
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => ApplicantResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(function ($record) {
                return ApplicantResource::getUrl('view', ['record' => $record]);
            });
    }
}
