<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApplicantGender: string implements HasLabel
{
    case Man = 'man';
    case Woman = 'woman';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Man => 'Hombre',
            self::Woman => 'Mujer',
        };
    }
}
