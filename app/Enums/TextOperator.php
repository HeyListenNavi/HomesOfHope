<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TextOperator: string implements HasLabel
{
    case Is = 'is';
    case IsNot = 'is_not';
    case Contains = 'contains';
    case DoesNotContain = 'does_not_contain';
    case IsEmpty = 'is_empty';
    case IsNotEmpty = 'is_not_empty';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Is => 'es igual a',
            self::IsNot => 'no es igual a',
            self::Contains => 'contiene la palabra',
            self::DoesNotContain => 'no contiene',
            self::IsEmpty => 'está vacío',
            self::IsNotEmpty => 'tiene contenido',
        };
    }
}
