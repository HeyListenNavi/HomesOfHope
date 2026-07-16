<?php

namespace App\Filament\Resources\FamilyMemberResource\Pages;

use App\Filament\Resources\FamilyMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFamilyMember extends ViewRecord
{
    protected static string $resource = FamilyMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
