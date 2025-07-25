<?php

namespace App\Filament\Resources\PartialApplicantResource\Pages;

use App\Filament\Resources\PartialApplicantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartialApplicants extends ListRecords
{
    protected static string $resource = PartialApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
