<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FamilyProfileTemplateSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Plantilla';
    }

    public function collection(): Collection
    {
        // Sample row to help the user
        return new Collection([
            [
                'Pérez López',           // family_name*
                'new',                   // status*
                '12/05/2026',           // opened_at*
                'Tijuana',              // home_city*
                'Tijuana',              // land_city*
                'si',                    // lives_on_land
                'El Florido',           // home_colony
                'Calle 123',            // home_address
                'https://maps...',      // home_address_link
                'El Florido',           // land_colony
                'Lote 45',              // land_address
                'https://maps...',      // land_address_link
                '2 años',               // land_ownership_time
                '50000',                // land_total_cost
                '5000',                 // land_down_payment
                '1000',                 // land_monthly_payment
                'mxn',                  // land_currency
                '10/05/2026',           // land_last_payment_date
                'si',                   // land_is_up_to_date
                'no',                   // land_is_flat
                'electricity, water',   // land_services
                'rented',               // home_status
                '1 año',                // home_ownership_time
                'Juan Pérez',           // home_owner_name
                '3000',                 // home_monthly_rent
                'mxn',                  // home_monthly_rent_currency
                'si',                   // home_has_receipts
                'Lámina',               // home_roof_material
                'fair',                 // home_roof_condition
                'Cemento',              // home_floor_material
                'good',                 // home_floor_condition
                'Madera',               // home_walls_material
                'poor',                 // home_walls_condition
                '2',                    // home_bedrooms_count
                '2 pequeños',           // home_bedrooms_description
                'outside',              // home_bathroom_location
                'Con letrina',          // home_bathroom_description
                'si',                   // home_furniture_owned
                'Estufa y cama',        // home_furniture_description
                'no',                   // has_addictions
                '',                     // addictions_details
                'Familia con mucha necesidad', // general_observations
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Nombre de Familia*', // family_name*
            'Estatus*', // status*
            'Fecha de Apertura*', // opened_at*
            'Ciudad de la Casa*', // home_city*
            'Ciudad del Terreno', // land_city
            'Vive en el Terreno? (si/no)', // lives_on_land
            'Colonia de la Casa', // home_colony
            'Dirección de la Casa', // home_address
            'Link de la Casa (Google Maps)', // home_address_link
            'Colonia del Terreno', // land_colony
            'Dirección del Terreno', // land_address
            'Link del Terreno (Google Maps)', // land_address_link
            'Tiempo con el Terreno', // land_ownership_time
            'Costo Total del Terreno', // land_total_cost
            'Enganche del Terreno', // land_down_payment
            'Costo mensual del Terreno', // land_monthly_payment
            'Moneda de pagos del Terreno', // land_currency
            'Fecha del Último Pago del Terreno', // land_last_payment_date
            'Está al corriente con el Terreno? (si/no)', // land_is_up_to_date
            'El Terreno esta plano? (si/no)', // land_is_flat
            'Servicios del Terreno', // land_services
            'Estado de la Casa', // home_status
            'Tiempo con la Casa', // home_ownership_time
            'Nombre del Propietario de la Casa', // home_owner_name
            'Costo mensual de la Casa', // home_monthly_rent
            'Moneda de la mensualidad de la Casa', // home_monthly_rent_currency
            'Tiene recibos los pagos de la Casa? (si/no)', // home_has_receipts
            'Material del Techo de la Casa', // home_roof_material
            'Estado del Techo de la Casa', // home_roof_condition
            'Material del Piso de la Casa', // home_floor_material
            'Estado del Piso de la Casa', // home_floor_condition
            'Material de las Paredes de la Casa', // home_walls_material
            'Estado de las Paredes de la Casa', // home_walls_condition
            'Cantidad de Habitaciones de la Casa', // home_bedrooms_count
            'Descripción de las Habitaciones de la Casa', // home_bedrooms_description
            'Ubicación del Baño de la Casa', // home_bathroom_location
            'Descripción del Baño de la Casa', // home_bathroom_description
            '¿Tiene muebles la Casa? (si/no)', // home_furniture_owned
            'Descripción de los Muebles de la Casa', // home_furniture_description
            '¿Tiene adicciones la Familia? (si/no)', // has_addictions
            'Detalles de las Adicciones', // addictions_details
            'Observaciones Generales', // general_observations
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
