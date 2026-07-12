<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApprovalRule: string implements HasLabel
{
    case ApproveIf = 'approve_if';
    case RejectIf = 'reject_if';
    case HumanIf = 'human_if';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ApproveIf => 'Aprobar automáticamente sí...',
            self::RejectIf => 'Rechazar automáticamente sí...',
            self::HumanIf => 'Solicitar revisión humana sí...',
        };
    }
}
