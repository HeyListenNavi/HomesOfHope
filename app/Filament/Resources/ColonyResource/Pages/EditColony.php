<?php

namespace App\Filament\Resources\ColonyResource\Pages;

use App\Filament\Resources\ColonyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditColony extends EditRecord
{
    protected static string $resource = ColonyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
