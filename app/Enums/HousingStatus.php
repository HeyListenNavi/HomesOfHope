<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum HousingStatus: string implements HasLabel
{
    case Rented = 'rented';
    case Borrowed = 'borrowed';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Rented => 'Rentada',
            self::Borrowed => 'Prestada',
            self::Other => 'Otro',
        };
    }
}
