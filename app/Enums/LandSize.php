<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LandSize: string implements HasLabel
{
    case Size16x20 = '16x20';
    case Size20x20 = '20x20';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Size16x20 => '16x20',
            self::Size20x20 => '20x20',
        };
    }
}
