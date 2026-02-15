<?php

namespace App\Imports;

use App\Models\Colony;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ColoniesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        return Colony::firstOrCreate(
            [
                'city' => trim($row['ciudad']),
                'name' => trim($row['colonia']),
            ],
            [
                'is_active' => true,
            ]
        );
    }
}
