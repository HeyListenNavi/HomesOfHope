<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasLabel, HasColor, HasIcon
{
    case Present = 'present';
    case Absent = 'absent';
    case Pending = 'pending';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Present => 'Presente',
            self::Absent => 'Ausente',
            self::Pending => 'Pendiente',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::Pending => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Present => 'heroicon-m-check-circle',
            self::Absent => 'heroicon-m-x-circle',
            self::Pending => 'heroicon-m-clock',
        };
    }
}
