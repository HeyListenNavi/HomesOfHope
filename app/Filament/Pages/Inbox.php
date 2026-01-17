<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class Inbox extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $title = 'Bandeja de Revisión';

    protected static ?string $navigationLabel = 'Bandeja de Revisión';

    protected static string $view = 'filament.pages.inbox';

    public static function getNavigationBadge(): ?string
    {
        return Applicant::where('process_status', 'requires_revision')->count() ?: null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applicant::query()
                    ->where('process_status', 'requires_revision')
                    ->latest('updated_at')
            )
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

                TextColumn::make('currentStage.name')
                    ->label('Etapa Detenida')
                    ->badge()
                    ->icon('heroicon-m-pause')
                    ->color('gray'),

                TextColumn::make('updated_at')
                    ->label('Tiempo en espera')
                    ->since()
                    ->sortable()
                    ->color('warning')
                    ->icon('heroicon-m-clock'),
            ])
            ->actions([
                Action::make('markAsRead')
                    ->label('Listo')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Revisión')
                    ->modalDescription('El aplicante volverá al estado "En Progreso" y saldrá de esta bandeja.')
                    ->action(function (Applicant $record) {
                        $record->update(['process_status' => 'in_progress']);

                        Notification::make()
                            ->title('Aplicante Procesado')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('markSelectedAsRead')
                        ->label('Marcar seleccionados como listos')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['process_status' => 'in_progress']);
                            Notification::make()->title('Aplicantes procesados')->success()->send();
                        }),
                ]),
            ])
            ->recordUrl(fn($record) => ApplicantResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('¡Todo al día!')
            ->emptyStateDescription('No hay aplicantes que requieran revisión manual por el momento.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
