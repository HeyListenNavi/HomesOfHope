<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EducationLevel: string implements HasLabel
{
    case Preschool = 'preschool';
    case None = 'none';
    case Elementary = 'elementary';
    case MiddleSchool = 'middle_school';
    case HighSchool = 'high_school';
    case University = 'university';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Preschool => 'Preescolar',
            self::None => 'Ninguno',
            self::Elementary => 'Primaria',
            self::MiddleSchool => 'Secundaria',
            self::HighSchool => 'Preparatoria',
            self::University => 'Universidad',
        };
    }
}
