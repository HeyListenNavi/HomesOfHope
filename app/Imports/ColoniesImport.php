<?php

namespace App\Imports;

use App\Models\Colony;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ColoniesImport implements SkipsEmptyRows, ToModel, WithHeadingRow
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
