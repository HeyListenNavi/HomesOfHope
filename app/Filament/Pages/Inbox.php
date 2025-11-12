<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Inbox extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static string $view = 'filament.pages.inbox';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Applicant::query()
                    ->where('process_status', 'requires_revision')
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('applicant_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('curp')
                    ->label('CURP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chat_id')
                    ->label('Teléfono')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currentStage.name')
                    ->label('Etapa')
                    ->sortable(),

                TextColumn::make('process_status')
                    ->label('Estado')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'requires_revision' => 'Requiere revisión',
                        'in_progress' => 'En progreso',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'canceled' => 'Cancelado',
                        default => $state,
                    }),
            ])
            ->actions([
                Action::make('markAsRead')
                    ->label('Revisado')
                    ->icon('heroicon-s-eye')
                    ->color('warning') 
                    ->requiresConfirmation()
                    ->modalHeading('Revisar Aplicante') 
                    ->modalDescription('¿Mover este aplicante a "En progreso"?')
                    ->modalSubmitActionLabel('Sí, revisar') 
                    ->action(function ($record) {
                        $record->update([
                            'process_status' => 'in_progress',
                        ]);

                        Notification::make()
                            ->title('Aplicante Revisado') 
                            ->body('El aplicante ahora está "En progreso".')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(function ($record) {
                return ApplicantResource::getUrl('view', ['record' => $record]);
            });
    }
}
