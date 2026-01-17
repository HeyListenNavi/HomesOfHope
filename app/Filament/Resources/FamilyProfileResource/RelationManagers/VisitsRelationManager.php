<?php

namespace App\Filament\Resources\FamilyProfileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Enums\FontWeight;
use App\Filament\Resources\VisitResource; // IMPORTANTE: Importar el recurso
use App\Models\Visit;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    protected static ?string $title = 'Visitas y Seguimiento';

    protected static ?string $icon = 'heroicon-s-map-pin';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('scheduled_at')
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Fecha')
                    ->dateTime('d M Y')
                    ->description(fn ($record) => $record->scheduled_at->format('h:i A'))
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\IconColumn::make('location_type')
                    ->label('Tipo')
                    ->icon(fn (string $state): string => match ($state) {
                        'home' => 'heroicon-s-home',
                        'office' => 'heroicon-s-building-office',
                        'virtual' => 'heroicon-s-video-camera',
                        default => 'heroicon-s-map-pin',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'home' => 'success',
                        'office' => 'info',
                        'virtual' => 'warning',
                        default => 'gray',
                    })
                    ->tooltip('Modalidad'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Programada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'Ausente',
                        'rescheduled' => 'Reprogramada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->counts('tasks')
                    ->label('Tareas')
                    ->badge()
                    ->icon('heroicon-s-clipboard-document-check')
                    ->color(fn ($state) => $state > 0 ? 'primary' : 'gray'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Agendar Visita')
                    ->icon('heroicon-s-plus')
                    ->url(fn ($livewire) => VisitResource::getUrl('create', [
                        'family_profile_id' => $livewire->getOwnerRecord()->id
                    ])),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->url(fn (Visit $record): string => VisitResource::getUrl('edit', ['record' => $record])),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }
}
