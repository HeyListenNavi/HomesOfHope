<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FamilyStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case Potential = 'potential';
    case InProcess = 'in_process';
    case OnHold = 'on_hold';
    case Approved = 'approved';
    case NotEligible = 'not_eligible';
    case Built = 'built';
    case DontBuild = 'dont_build';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::New => 'Nuevo',
            self::Potential => 'Potencial',
            self::InProcess => 'En Proceso',
            self::OnHold => 'En Espera',
            self::Approved => 'Aprobado',
            self::NotEligible => 'No Califica',
            self::Built => 'Construido',
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
            self::NotEligible => 'heroicon-s-lock-closed',
            self::Built => 'heroicon-s-building-office-2',
            self::DontBuild => 'heroicon-s-x-circle',
        };
    }
}
