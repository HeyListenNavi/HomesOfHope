<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class FamilyForm extends Form
{
    #[Validate('required', message: 'Por favor ingresa el nombre de la familia.')]
    #[Validate('min:3', message: 'El nombre de la familia debe tener al menos 3 caracteres.')]
    public string $name = '';

    #[Validate('required', message: 'Por favor indica si viven en el terreno.')]
    #[Validate('boolean')]
    public ?bool $lives_on_land = null;

    #[Validate('required', message: 'Por favor indica si los papás están casados por el civil.')]
    #[Validate('boolean')]
    public ?bool $parents_married = null;

    #[Validate('required', message: 'Indica cuántas personas vivirán en la casa.')]
    #[Validate('integer')]
    #[Validate('min:1', message: 'Debe haber al menos 1 persona.')]
    public int $member_count = 1;

    #[Validate('nullable', message: 'Por favor indica si existe presencia de adicciones.')]
    #[Validate('boolean')]
    public ?bool $has_addictions = null;

    #[Validate('required_if:has_addictions,true|nullable|string', message: 'Por favor proporciona los detalles.')]
    public ?string $addictions_details = null;

    public function toData(): array
    {
        return $this->only([
            'name',
            'lives_on_land',
            'parents_married',
            'member_count',
            'has_addictions',
            'addictions_details',
        ]);
    }
}
