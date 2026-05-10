<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Relationship: string implements HasLabel, HasColor
{
    case Father = 'padre';
    case Mother = 'madre';
    case Child = 'hijo';
    case Grandparent = 'abuelo';
    case Grandchild = 'nieto';
    case Other = 'otro';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Father => '👨 Padre',
            self::Mother => '👩 Madre',
            self::Child => '👶 Hijo(a)',
            self::Grandparent => '👴 Abuelo(a)',
            self::Grandchild => '🧸 Nieto(a)',
            self::Other => '👤 Otro',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Father, self::Mother => 'primary',
            self::Child, self::Grandchild => 'info',
            self::Grandparent => 'warning',
            self::Other => 'gray',
        };
    }
}
