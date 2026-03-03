<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('¿Eliminar este rol permanentemente?')
                ->modalDescription('Esta acción no se puede deshacer. Todos los usuarios asociados a este rol perderán sus permisos de acceso inmediatamente.')
                ->modalSubmitActionLabel('Sí, eliminar rol')
                ->modalIcon('heroicon-o-trash')
                ->hidden(fn($record) => $record->name === 'admin'),
        ];
    }
}
