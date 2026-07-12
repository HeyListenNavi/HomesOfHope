<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConditionLevel: string implements HasLabel
{
    case Good = 'good';
    case Fair = 'fair';
    case Poor = 'poor';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Good => 'Bueno',
            self::Fair => 'Regular',
            self::Poor => 'Malo / Precario',
        };
    }
}
