<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ApplicantsRelationManager extends RelationManager
{
    protected static string $relationship = 'applicants';

    protected static ?string $title = 'Miembros del Grupo';
    protected static ?string $icon = 'heroicon-m-users';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('applicant_name', 'asc')
            ->columns([
                TextColumn::make('applicant_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make("curp")
                    ->label('CURP')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->formatStateUsing(fn(string $state) => strtoupper($state))
                    ->color('gray'),

                TextColumn::make('gender')
                    ->label('Género')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'man' => Color::hex('#3b82f6'),
                        'woman' => Color::hex('#ec4899'),
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'man' => 'Hombre',
                        'woman' => 'Mujer',
                        default => 'Otro',
                    }),

                TextColumn::make('chat_id')
                    ->label('Número de Telefono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('gray')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';

                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn($state) => 'https://wa.me/' . $state)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('assignApplicant')
                    ->label('Agregar Miembro')
                    ->icon('heroicon-m-user-plus')
                    ->color('primary')
                    ->form([
                        Select::make('applicant_id')
                            ->label('Seleccionar Solicitante')
                            ->helperText('Solo se muestran aplicantes sin grupo asignado.')
                            ->options(fn() => Applicant::whereNull('group_id')
                                ->whereNotNull('applicant_name')
                                ->orderBy('applicant_name')
                                ->pluck('applicant_name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $group = $this->getOwnerRecord();

                        if ($group->applicants()->count() >= $group->capacity) {
                            Notification::make()
                                ->title('Grupo Lleno')
                                ->body("Este grupo ya alcanzó su capacidad máxima de {$group->capacity} miembros.")
                                ->danger()
                                ->send();

                            return;
                        }

                        $applicant = Applicant::find($data['applicant_id']);
                        $applicant->update(['group_id' => $group->id]);

                        Notification::make()
                            ->title('Miembro Agregado')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->url(fn($record) => ApplicantResource::getUrl('edit', ['record' => $record]))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('removeFromGroup')
                        ->label('Quitar del Grupo')
                        ->icon('heroicon-m-arrow-right-on-rectangle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Remover miembro')
                        ->modalDescription('El aplicante dejará de pertenecer a este grupo, pero su ficha no será eliminada.')
                        ->action(fn(Applicant $record) => $record->update(['group_id' => null])),

                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar Definitivamente'),
                ])
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkRemove')
                        ->label('Quitar seleccionados')
                        ->icon('heroicon-m-arrow-right-on-rectangle')
                        ->requiresConfirmation()
                        ->action(fn(Collection $records) => $records->each->update(['group_id' => null])),
                ]),
            ])
            ->emptyStateHeading('Sin miembros asignados')
            ->emptyStateDescription('Utiliza el botón "Agregar Miembro" para comenzar a llenar este grupo.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
