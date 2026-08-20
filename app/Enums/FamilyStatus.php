<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FamilyStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case InProcess = 'in_process';
    case OnHold = 'on_hold';
    case Potential = 'potential';
    case Approved = 'approved';
    case Programmed = 'programmed';
    case Built = 'built';
    case NotEligible = 'not_eligible';
    case DontBuild = 'dont_build';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::New => 'Nuevo',
            self::InProcess => 'En Proceso',
            self::OnHold => 'En Espera',
            self::Potential => 'Potencial',
            self::Approved => 'Aprobado',
            self::Programmed => 'Programado',
            self::Built => 'Construido',
            self::NotEligible => 'No Calificado',
            self::DontBuild => 'No Elegible',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::New => 'gray',
            self::Potential => 'info',
            self::InProcess, self::OnHold => 'warning',
            self::Approved => 'success',
            self::Programmed => 'info',
            self::NotEligible, self::DontBuild => 'danger',
            self::Built => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-s-plus-circle',
            self::Potential => 'heroicon-s-eye',
            self::InProcess => 'heroicon-s-arrow-path',
            self::OnHold => 'heroicon-s-pause-circle',
            self::Approved => 'heroicon-s-check-circle',
            self::Programmed => 'heroicon-s-calendar-days',
            self::NotEligible => 'heroicon-s-lock-closed',
            self::Built => 'heroicon-s-building-office-2',
            self::DontBuild => 'heroicon-s-x-circle',
        };
    }
}
