<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FamilyProfileTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new FamilyProfileTemplateSheet,
            new OptionsReferenceSheet,
        ];
    }
}
