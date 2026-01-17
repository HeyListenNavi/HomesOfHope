<?php

namespace App\Filament\Pages;

use App\Models\Applicant;
use App\Filament\Resources\ApplicantResource;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Columns\TextColumn\TextColumnSize;

class AiRevision extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $title = 'Bandeja de Revisión IA';

    protected static ?string $navigationLabel = 'Bandeja de Revisión IA';

    public ?string $activeTab = 'all';

    protected static string $view = 'filament.pages.ai-revision';

    public static function getNavigationBadge(): ?string
    {
        return Applicant::whereIn('process_status', ['approved', 'rejected'])->count() ?: null;
    }

    protected function getTableQuery(): Builder
    {
        $query = Applicant::query()
            ->latest('updated_at');

        return match ($this->activeTab) {
            'approved' => $query->where('process_status', 'approved'),
            'rejected' => $query->where('process_status', 'rejected'),
            default    => $query->whereIn('process_status', ['approved', 'rejected']),
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('applicant_name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('chat_id')
                    ->label('Número de Telefono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn($state) => 'https://wa.me/' . $state)
                    ->openUrlInNewTab()
                    ->searchable(),

                TextColumn::make('process_status')
                    ->label('Decisión IA')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'IA: Aprobó',
                        'rejected' => 'IA: Rechazó',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'approved' => 'heroicon-m-sparkles',
                        'rejected' => 'heroicon-m-x-mark',
                        default => 'heroicon-m-question-mark-circle',
                    }),

                TextColumn::make('updated_at')
                    ->label('Tiempo en espera')
                    ->since()
                    ->sortable()
                    ->color('warning')
                    ->icon('heroicon-m-clock'),
            ])
            ->actions([
                // ACCIÓN 1: VALIDAR (Staff confirma la decisión o corrige a la IA)
                Action::make('staff_approve')
                    ->label('Aprobar')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-m-check-badge')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobación Definitiva')
                    ->modalDescription(fn($record) => $record->process_status === 'rejected'
                        ? 'ATENCIÓN: La IA rechazó a esta persona. ¿Estás seguro de corregir a la IA y APROBAR manualmenete?'
                        : 'Confirmas que este aplicante pasa a la siguiente etapa (Selección de Grupo).')
                    ->action(function (Applicant $record) {
                        $record->update([
                            'process_status' => 'staff_approved', // Estado final positivo
                            'rejection_reason' => null
                        ]);
                        Notification::make()->title('Validado por Staff')->success()->send();
                    }),

                // ACCIÓN 2: RECHAZAR (Staff confirma el rechazo o corrige a la IA)
                Action::make('staff_reject')
                    ->label('Rechazar')
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-m-x-circle')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->rows(3)
                            ->default(fn($record) => $record->rejection_reason) // Pre-llenar si la IA ya dio una razón
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Rechazo Definitivo')
                    ->action(function (Applicant $record, array $data) {
                        $record->update([
                            'process_status' => 'staff_rejected', // Estado final negativo
                            'rejection_reason' => $data['rejection_reason']
                        ]);
                        Notification::make()->title('Rechazado por Staff')->danger()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('markSelectedAsApproved')
                        ->label('Validar Seleccionados')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['process_status' => 'staff_approved']);
                            Notification::make()->title('Aplicantes validados')->success()->send();
                        }),
                ]),
            ])
            ->recordUrl(fn($record) => ApplicantResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Bandeja limpia')
            ->emptyStateDescription('Has revisado todas las decisiones de la IA.');
    }
}
