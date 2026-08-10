<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class HomeForm extends Form
{
    #[Validate('required', message: 'Selecciona la ciudad de la casa actual.')]
    #[Validate('in:Tijuana,Rosarito', message: 'Selecciona una ciudad válida.')]
    public string $city = '';

    #[Validate('required', message: 'Ingresa la colonia de la casa actual.')]
    public string $colony = '';

    #[Validate('required', message: 'Ingresa la dirección de la casa actual.')]
    public string $address = '';

    #[Validate('required', message: 'Por favor usa el GPS o el mapa para ubicar la casa.')]
    #[Validate('numeric', message: 'La latitud debe ser un número válido.')]
    public ?float $lat = null;

    #[Validate('required', message: 'Por favor usa el GPS o el mapa para ubicar la casa.')]
    #[Validate('numeric', message: 'La longitud debe ser un número válido.')]
    public ?float $lng = null;

    #[Validate('nullable|string')]
    public ?string $status = null;

    #[Validate('nullable|string')]
    public ?string $ownership_time = null;

    #[Validate('nullable|string')]
    public ?string $owner_name = null;

    #[Validate('nullable')]
    #[Validate('numeric', message: 'La renta mensual debe ser un número.')]
    #[Validate('min:0', message: 'La renta mensual no puede ser negativa.')]
    public ?string $monthly_rent = null;

    #[Validate('nullable|string')]
    public string $monthly_rent_currency = 'mxn';

    #[Validate('nullable|boolean')]
    public ?bool $has_receipts = null;

    #[Validate('nullable|string')]
    public ?string $description = null;

    public function toData(): array
    {
        return $this->only([
            'city',
            'colony',
            'address',
            'lat',
            'lng',
            'status',
            'ownership_time',
            'owner_name',
            'monthly_rent',
            'monthly_rent_currency',
            'has_receipts',
            'description',
        ]);
    }
}
