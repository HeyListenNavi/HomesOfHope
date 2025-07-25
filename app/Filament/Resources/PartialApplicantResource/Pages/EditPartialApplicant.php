<?php

namespace App\Filament\Resources\PartialApplicantResource\Pages;

use App\Filament\Resources\PartialApplicantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartialApplicant extends EditRecord
{
    protected static string $resource = PartialApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
