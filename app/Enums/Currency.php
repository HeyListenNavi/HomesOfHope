<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Currency: string implements HasLabel
{
    case MXN = 'mxn';
    case USD = 'usd';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MXN => 'MXN ($)',
            self::USD => 'USD ($)',
        };
    }
}
