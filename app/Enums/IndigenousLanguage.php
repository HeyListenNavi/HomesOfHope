<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IndigenousLanguage: string implements HasLabel
{
    case Nahuatl = 'nahuatl';
    case Maya = 'maya';
    case Zapoteco = 'zapoteco';
    case Mixteco = 'mixteco';
    case Tseltal = 'tseltal';
    case Tsotsil = 'tsotsil';
    case Otomi = 'otomi';
    case Totonaco = 'totonaco';
    case Mazateco = 'mazateco';
    case Chol = 'chol';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Nahuatl => 'Náhuatl',
            self::Maya => 'Maya',
            self::Zapoteco => 'Zapoteco',
            self::Mixteco => 'Mixteco',
            self::Tseltal => 'Tseltal',
            self::Tsotsil => 'Tsotsil',
            self::Otomi => 'Otomí',
            self::Totonaco => 'Totonaco',
            self::Mazateco => 'Mazateco',
            self::Chol => 'Chol',
            self::Other => 'Otro',
        };
    }
}
