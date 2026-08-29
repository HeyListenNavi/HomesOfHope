<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TagType: string implements HasColor, HasLabel
{
    case Applicant = 'applicant';
    case FamilyProfile = 'family_profile';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Applicant => 'Aplicantes',
            self::FamilyProfile => 'Familias',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Applicant => 'info',
            self::FamilyProfile => 'success',
        };
    }
}
