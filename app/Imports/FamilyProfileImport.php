<?php

namespace App\Imports;

use App\Models\FamilyProfile;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class FamilyProfileImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new FamilyProfileSheetImport,
        ];
    }
}

class FamilyProfileSheetImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $data = [];
        foreach ($row as $key => $value) {
            $cleanKey = trim(str_replace('*', '', $key));
            $data[$cleanKey] = $value;
        }

        return new FamilyProfile([
            'family_name' => $data['family_name'] ?? null,
            'status' => $data['status'] ?? 'new',
            'opened_at' => $this->transformDate($data['opened_at'] ?? null) ?? now(),
            'home_city' => $data['home_city'] ?? null,
            'land_city' => $data['land_city'] ?? null,
            'lives_on_land' => $this->transformBoolean($data['lives_on_land'] ?? null),
            'home_colony' => $data['home_colony'] ?? null,
            'home_address' => $data['home_address'] ?? null,
            'home_address_link' => $data['home_address_link'] ?? null,
            'land_colony' => $data['land_colony'] ?? null,
            'land_address' => $data['land_address'] ?? null,
            'land_address_link' => $data['land_address_link'] ?? null,
            'land_ownership_time' => $data['land_ownership_time'] ?? null,
            'land_total_cost' => $data['land_total_cost'] ?? null,
            'land_down_payment' => $data['land_down_payment'] ?? null,
            'land_monthly_payment' => $data['land_monthly_payment'] ?? null,
            'land_currency' => $data['land_currency'] ?? 'mxn',
            'land_last_payment_date' => $this->transformDate($data['land_last_payment_date'] ?? null),
            'land_is_up_to_date' => $this->transformBoolean($data['land_is_up_to_date'] ?? null),
            'land_is_flat' => $this->transformBoolean($data['land_is_flat'] ?? null),
            'land_services' => $this->transformArray($data['land_services'] ?? null),
            'home_status' => $data['home_status'] ?? null,
            'home_ownership_time' => $data['home_ownership_time'] ?? null,
            'home_owner_name' => $data['home_owner_name'] ?? null,
            'home_monthly_rent' => $data['home_monthly_rent'] ?? null,
            'home_monthly_rent_currency' => $data['home_monthly_rent_currency'] ?? 'mxn',
            'home_has_receipts' => $this->transformBoolean($data['home_has_receipts'] ?? null),
            'home_roof_material' => $data['home_roof_material'] ?? null,
            'home_roof_condition' => $data['home_roof_condition'] ?? null,
            'home_floor_material' => $data['home_floor_material'] ?? null,
            'home_floor_condition' => $data['home_floor_condition'] ?? null,
            'home_walls_material' => $data['home_walls_material'] ?? null,
            'home_walls_condition' => $data['home_walls_condition'] ?? null,
            'home_bedrooms_count' => $data['home_bedrooms_count'] ?? null,
            'home_bedrooms_description' => $data['home_bedrooms_description'] ?? null,
            'home_bathroom_location' => $data['home_bathroom_location'] ?? null,
            'home_bathroom_description' => $data['home_bathroom_description'] ?? null,
            'home_furniture_owned' => $this->transformBoolean($data['home_furniture_owned'] ?? null),
            'home_furniture_description' => $data['home_furniture_description'] ?? null,
            'has_addictions' => $this->transformBoolean($data['has_addictions'] ?? null),
            'addictions_details' => $data['addictions_details'] ?? null,
            'general_observations' => $data['general_observations'] ?? null,
        ]);
    }

    private function transformBoolean($value)
    {
        if (is_null($value)) {
            return false;
        }
        if (is_bool($value)) {
            return $value;
        }
        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'si', 'sí', 'on']);
    }

    private function transformArray($value)
    {
        if (is_null($value)) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }

        return array_map('trim', explode(',', (string) $value));
    }

    private function transformDate($value)
    {
        if (is_null($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value));
            }

            return Carbon::parse((string) $value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
