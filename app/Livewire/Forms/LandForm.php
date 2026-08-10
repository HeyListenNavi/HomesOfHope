<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class LandForm extends Form
{
    #[Validate('required', message: 'Selecciona la ciudad del terreno.')]
    #[Validate('in:Tijuana,Rosarito', message: 'Selecciona una ciudad válida.')]
    public string $city = '';

    #[Validate('required', message: 'Ingresa la colonia del terreno.')]
    public string $colony = '';

    #[Validate('required', message: 'Ingresa la dirección del terreno.')]
    public string $address = '';

    #[Validate('required', message: 'Por favor usa el GPS o el mapa para ubicar el terreno.')]
    #[Validate('numeric', message: 'La latitud debe ser un número válido.')]
    public ?float $lat = null;

    #[Validate('required', message: 'Por favor usa el GPS o el mapa para ubicar el terreno.')]
    #[Validate('numeric', message: 'La longitud debe ser un número válido.')]
    public ?float $lng = null;

    #[Validate('nullable|string')]
    public ?string $ownership_time = null;

    #[Validate('nullable')]
    #[Validate('numeric', message: 'El costo total debe ser un número.')]
    #[Validate('min:0', message: 'El costo total no puede ser negativo.')]
    public ?string $total_cost = null;

    #[Validate('nullable')]
    #[Validate('numeric', message: 'El enganche debe ser un número.')]
    #[Validate('min:0', message: 'El enganche no puede ser negativo.')]
    public ?string $down_payment = null;

    #[Validate('nullable')]
    #[Validate('numeric', message: 'La mensualidad debe ser un número.')]
    #[Validate('min:0', message: 'La mensualidad no puede ser negativa.')]
    public ?string $monthly_payment = null;

    #[Validate('nullable|string')]
    public string $currency = 'mxn';

    #[Validate('nullable')]
    #[Validate('date', message: 'Ingresa una fecha válida.')]
    public ?string $last_payment_date = null;

    #[Validate('nullable|boolean')]
    public ?bool $is_up_to_date = null;

    #[Validate('nullable|boolean')]
    public ?bool $is_flat = null;

    #[Validate('nullable|array')]
    public array $services = [];

    public function toData(): array
    {
        return $this->only([
            'city',
            'colony',
            'address',
            'lat',
            'lng',
            'ownership_time',
            'total_cost',
            'down_payment',
            'monthly_payment',
            'currency',
            'last_payment_date',
            'is_up_to_date',
            'is_flat',
            'services',
        ]);
    }
}
