<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Religion: string implements HasLabel
{
    case Catholic = 'catholic';
    case Christian = 'christian';
    case JehovahsWitness = 'jehovahs_witness';
    case Mormon = 'mormon';
    case None = 'none';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Catholic => 'Católica',
            self::Christian => 'Cristiana',
            self::JehovahsWitness => 'Testigo de Jehová',
            self::Mormon => 'Mormón',
            self::None => 'Ninguna',
            self::Other => 'Otra',
        };
    }
}
