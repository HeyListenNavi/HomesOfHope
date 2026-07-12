<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MessageRole: string implements HasColor, HasIcon, HasLabel
{
    case User = 'user';
    case Assistant = 'assistant';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::User => 'Usuario',
            self::Assistant => 'Bot',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::User => 'info',
            self::Assistant => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::User => 'heroicon-m-user',
            self::Assistant => 'heroicon-m-cpu-chip',
        };
    }
}
