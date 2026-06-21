<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ApplicantStatus: string implements HasColor, HasIcon, HasLabel
{
    case InProgress = 'in_progress';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case StaffApproved = 'staff_approved';
    case StaffRejected = 'staff_rejected';
    case RequiresRevision = 'requires_revision';
    case Canceled = 'canceled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::InProgress => 'En Progreso',
            self::Approved => 'IA: Aprobado',
            self::Rejected => 'IA: Rechazado',
            self::StaffApproved => 'Staff: Aprobado',
            self::StaffRejected => 'Staff: Rechazado',
            self::RequiresRevision => 'Requiere Revisión',
            self::Canceled => 'Cancelado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::InProgress => 'info',
            self::Approved => 'success',
            self::StaffApproved => 'success',
            self::Rejected => 'danger',
            self::StaffRejected => 'danger',
            self::RequiresRevision => 'warning',
            self::Canceled => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::InProgress => 'heroicon-m-arrow-path',
            self::Approved => 'heroicon-m-sparkles',
            self::StaffApproved => 'heroicon-m-check-badge',
            self::Rejected => 'heroicon-m-x-circle',
            self::StaffRejected => 'heroicon-m-no-symbol',
            self::RequiresRevision => 'heroicon-m-exclamation-triangle',
            self::Canceled => 'heroicon-m-x-mark',
        };
    }
}
