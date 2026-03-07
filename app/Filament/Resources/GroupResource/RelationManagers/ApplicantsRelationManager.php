<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use App\Models\Question;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ApplicantsRelationManager extends RelationManager
{
    protected static string $relationship = 'applicants';

    protected static ?string $title = 'Miembros del Grupo';
    protected static ?string $icon = 'heroicon-m-users';

    public function table(Table $table): Table
    {
        $columns = [
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
                    ->searchable()
                    ->toggleable(),
        ];

        $dynamicQuestions = Question::orderBy('order', 'asc')->get();

        foreach ($dynamicQuestions->values() as $index => $question) {
            $columns[] =
            TextColumn::make('dynamic_question_' . $question->id)
                ->label('Pregunta ' . ($index + 1))
                ->size(TextColumn\TextColumnSize::ExtraSmall)
                ->searchable()
                ->formatStateUsing(fn (string $state) => self::extractLocationUrl($state) ? '📍 Ver en Mapa' : str($state)->limit(90))
                ->color(fn (string $state) => self::extractLocationUrl($state) ? 'primary' : null)
                ->url(fn (string $state) => self::extractLocationUrl($state))
                ->openUrlInNewTab()
                ->state(function (Applicant $record) use ($question) {
                    $response = $record->responses->firstWhere('question_id', $question->id);

                    return $response ? $response->user_response : null;
                })
                ->placeholder('No respondida')
                ->limit(30)
                ->toggleable(isToggledHiddenByDefault: true);
        }

        array_push($columns,
            TextColumn::make('currentStage.name')
                    ->label('Etapa')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                ->label('Fecha de Registro')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label('Última Actualización')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        );

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('responses'))
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->columns($columns)
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

    public static function extractLocationUrl(?string $state): ?string
    {
        if (empty($state)) return null;

        $cleanState = trim($state);

        // Notas de Vero real:

        // Match a un link de maps
        if (preg_match('/(https?:\/\/(www\.)?google\.[a-z.]+\/maps\/[^\s]+|https?:\/\/goo\.gl\/maps\/[^\s]+|https?:\/\/maps\.app\.goo\.gl\/[^\s]+)/i', $cleanState, $matches)) {
            return $matches[0];
        }

        // Match a coordenadas
        if (preg_match('/(?<![\d.\-+])[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)(?![\d.])/', $cleanState, $matches)) {
            return $cache[$state] = "https://maps.google.com/?q=" . urlencode(trim($matches[0]));
        }

        // Match a Plus Code
        if (preg_match('/([23456789C][23456789CFGHJMPQRV][23456789CFGHJMPQRVWX]{6}\+[23456789CFGHJMPQRVWX]{2,7}|[23456789CFGHJMPQRVWX]{4,6}\+[23456789CFGHJMPQRVWX]{2,3})/i', $cleanState, $matches)) {
            return "https://maps.google.com/?q=" . urlencode($matches[0]);
        }

        return $cache[$state] = null;
    }

    public function canView(Model $record): bool
    {
        return auth()->user()?->can('applicant.view') ?? false;
    }

    public function canCreate(): bool
    {
        return auth()->user()?->can('applicant.create') ?? false;
    }

    public function canEdit(Model $record): bool
    {
        return auth()->user()?->can('applicant.update') ?? false;
    }

    public function canDelete(Model $record): bool
    {
        return auth()->user()?->can('applicant.delete') ?? false;
    }
}
