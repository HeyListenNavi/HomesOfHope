<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Present = 'present';
    case Attended = 'attended';
    case Absent = 'absent';
    case Pending = 'pending';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Present => 'Presente',
            self::Attended => 'Atendido',
            self::Absent => 'Ausente',
            self::Pending => 'Pendiente',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Present => 'info',
            self::Attended => 'success',
            self::Absent => 'danger',
            self::Pending => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Present => 'heroicon-m-check-circle',
            self::Attended => 'heroicon-m-check-badge',
            self::Absent => 'heroicon-m-x-circle',
            self::Pending => 'heroicon-m-clock',
        };
    }
}
