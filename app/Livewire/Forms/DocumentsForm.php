<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class DocumentsForm extends Form
{
    public $family_photo = null;

    public $marriage_certificate = null;

    public $land_ownership = null;

    public array $land_receipts = [];

    public array $new_land_receipts = [];

    public function removeLandReceipt(int $index): void
    {
        unset($this->land_receipts[$index]);
        $this->land_receipts = array_values($this->land_receipts);
    }

    public function syncNewReceipts(): void
    {
        if (! is_array($this->new_land_receipts)) {
            $this->new_land_receipts = [$this->new_land_receipts];
        }

        foreach ($this->new_land_receipts as $receipt) {
            if (count($this->land_receipts) < 5) {
                $this->land_receipts[] = $receipt;
            }
        }
        $this->new_land_receipts = [];
    }

    public function resolveRules(bool $parentsMarried): array
    {
        $rules = [
            'docs.family_photo' => 'nullable|mimes:jpg,jpeg,png,webp,heic|max:10240',
            'docs.land_ownership' => 'nullable|mimes:jpg,jpeg,png,webp,heic,pdf|max:10240',
            'docs.land_receipts' => 'nullable|array|max:5',
            'docs.land_receipts.*' => 'mimes:jpg,jpeg,png,webp,heic,pdf|max:10240',
        ];

        if ($parentsMarried) {
            $rules['docs.marriage_certificate'] = 'nullable|mimes:jpg,jpeg,png,webp,heic,pdf|max:10240';
        }

        return $rules;
    }

    public function resolveMessages(bool $parentsMarried): array
    {
        $messages = [
            'docs.family_photo.mimes' => 'La foto familiar debe ser una imagen.',
            'docs.land_ownership.mimes' => 'El contrato o título debe ser imagen o PDF.',
            'docs.land_receipts.max' => 'Puedes subir un máximo de 5 recibos.',
            'docs.land_receipts.*.mimes' => 'Los recibos deben ser imágenes o PDF.',
        ];

        if ($parentsMarried) {
            $messages['docs.marriage_certificate.mimes'] = 'El acta de matrimonio debe ser imagen o PDF.';
        }

        return $messages;
    }
}
