<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\ApplicantResource;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Registro de Asistencias';

    protected static ?string $icon = 'heroicon-m-clipboard-document-check';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('attendance_code')
                    ->label('Código de Asistencia')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Estatus')
                    ->options(AttendanceStatus::class)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('applicant'))
            ->recordTitleAttribute('attendance_code')
            ->recordUrl(fn (Attendance $record): ?string => $record->applicant ? ApplicantResource::getUrl('edit', ['record' => $record->applicant]) : null)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('applicant.applicant_name')
                    ->label('Aplicante')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->placeholder('Sin aplicante vinculado'),

                TextColumn::make('applicant.chat_id')
                    ->label('Teléfono')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        return str_starts_with($state, '521') ? substr($state, 3) : $state;
                    })
                    ->url(fn ($state) => $state ? 'https://wa.me/'.$state : null)
                    ->openUrlInNewTab(),

                TextColumn::make('attendance_code')
                    ->label('Código')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Código copiado al portapapeles'),

                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn (?AttendanceStatus $state): ?string => $state?->getLabel() ?? 'Pendiente')
                    ->color(fn (?AttendanceStatus $state): string|array|null => $state?->getColor() ?? 'gray')
                    ->icon(fn (?AttendanceStatus $state): ?string => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('scanned_at')
                    ->label('Escaneado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No escaneado')
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estatus')
                    ->options(AttendanceStatus::class),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('markPresent')
                        ->label('Marcar Presente')
                        ->icon('heroicon-m-check-circle')
                        ->color('info')
                        ->action(function (Attendance $record) {
                            $record->update([
                                'status' => AttendanceStatus::Present,
                                'scanned_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Asistencia marcada como Presente')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Attendance $record) => $record->status !== AttendanceStatus::Present),

                    Tables\Actions\Action::make('markAttended')
                        ->label('Marcar Atendido')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->action(function (Attendance $record) {
                            $record->update([
                                'status' => AttendanceStatus::Attended,
                                'scanned_at' => $record->scanned_at ?? now(),
                            ]);

                            Notification::make()
                                ->title('Asistencia marcada como Atendido')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Attendance $record) => $record->status !== AttendanceStatus::Attended),

                    Tables\Actions\Action::make('markAbsent')
                        ->label('Marcar Ausente')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(function (Attendance $record) {
                            $record->update([
                                'status' => AttendanceStatus::Absent,
                                'scanned_at' => null,
                            ]);

                            Notification::make()
                                ->title('Asistencia marcada como Ausente')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Attendance $record) => $record->status !== AttendanceStatus::Absent),

                    Tables\Actions\Action::make('viewApplicant')
                        ->label('Ver Ficha del Aplicante')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->url(fn (Attendance $record) => $record->applicant ? ApplicantResource::getUrl('view', ['record' => $record->applicant]) : null)
                        ->openUrlInNewTab()
                        ->visible(fn (Attendance $record) => $record->applicant !== null),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Sin asistencias registradas')
            ->emptyStateDescription('Las asistencias se registran automáticamente cuando los aplicantes eligen este grupo o son escaneados en el pase de lista.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }

    public function canView(Model $record): bool
    {
        return auth()->user()?->can('group.view') ?? true;
    }
}
