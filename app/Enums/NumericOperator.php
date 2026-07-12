<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NumericOperator: string implements HasLabel
{
    case IsEqualTo = 'is_equal_to';
    case IsGreaterThan = 'is_greater_than';
    case IsLessThan = 'is_less_than';
    case IsGreaterThanOrEqualTo = 'is_greater_than_or_equal_to';
    case IsLessThanOrEqualTo = 'is_less_than_or_equal_to';
    case Between = 'between';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IsEqualTo => '= igual a',
            self::IsGreaterThan => '> mayor que',
            self::IsLessThan => '< menor que',
            self::IsGreaterThanOrEqualTo => '>= mayor o igual',
            self::IsLessThanOrEqualTo => '<= menor o igual',
            self::Between => 'está entre rango',
        };
    }
}
