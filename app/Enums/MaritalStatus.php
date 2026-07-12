<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaritalStatus: string implements HasLabel
{
    case Single = 'single';
    case Married = 'married';
    case Divorced = 'divorced';
    case Widowed = 'widowed';
    case Cohabiting = 'cohabiting';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Single => 'Soltero(a)',
            self::Married => 'Casado(a)',
            self::Divorced => 'Divorciado(a)',
            self::Widowed => 'Viudo(a)',
            self::Cohabiting => 'Unión Libre',
        };
    }
}
