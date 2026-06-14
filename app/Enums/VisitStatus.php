<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum VisitStatus: string implements HasLabel, HasColor, HasIcon
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';
    case Rescheduled = 'rescheduled';
    case Pending = 'pending';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Scheduled => 'Programada',
            self::Completed => 'Completada',
            self::Cancelled => 'Cancelada',
            self::NoShow => 'No se presentó',
            self::Rescheduled => 'Reprogramar',
            self::Pending => 'Por Visitar',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Scheduled => 'info',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::NoShow => 'warning',
            self::Rescheduled => 'gray',
            self::Pending => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Scheduled => 'heroicon-s-calendar',
            self::Completed => 'heroicon-s-check-circle',
            self::Cancelled => 'heroicon-s-x-circle',
            self::NoShow => 'heroicon-s-eye-slash',
            self::Rescheduled => 'heroicon-s-arrow-path',
            self::Pending => 'heroicon-s-clock',
        };
    }
}
