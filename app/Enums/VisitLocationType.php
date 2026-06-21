<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum VisitLocationType: string implements HasColor, HasIcon, HasLabel
{
    case Home = 'home';
    case Office = 'office';
    case Virtual = 'virtual';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Home => 'Terreno',
            self::Office => 'Oficina',
            self::Virtual => 'Virtual',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Home => 'success',
            self::Office => 'info',
            self::Virtual => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Home => 'heroicon-s-home',
            self::Office => 'heroicon-s-building-office',
            self::Virtual => 'heroicon-s-phone',
        };
    }
}
