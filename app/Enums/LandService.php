<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LandService: string implements HasLabel
{
    case Electricity = 'electricity';
    case Water = 'water';
    case SepticTank = 'septic_tank';
    case Sewage = 'sewage';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Electricity => 'Luz eléctrica',
            self::Water => 'Agua potable',
            self::SepticTank => 'Fosa séptica',
            self::Sewage => 'Drenaje municipal',
        };
    }
}
