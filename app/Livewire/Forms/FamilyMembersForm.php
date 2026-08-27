<?php

namespace App\Livewire\Forms;

use App\Enums\EducationLevel;
use App\Enums\IndigenousLanguage;
use App\Enums\MaritalStatus;
use App\Enums\Occupation;
use App\Enums\Relationship;
use App\Enums\Religion;
use App\Models\Applicant;
use App\Rules\UniqueFamilyCurp;
use Illuminate\Validation\Rule;
use Livewire\Form;

class FamilyMembersForm extends Form
{
    public array $list = [];

    public function prefillSelfMember(Applicant $applicant): void
    {
        $relationship = match ($applicant->gender) {
            'man' => Relationship::Father->value,
            'woman' => Relationship::Mother->value,
            default => Relationship::Other->value,
        };

        $this->list[] = $this->memberRow([
            'relationship' => $relationship,
            'curp' => $applicant->curp ?? '',
            'is_responsible' => true,
        ]);
    }

    public function syncCount(int $count): void
    {
        $count = max(1, min(20, $count));

        while (count($this->list) < $count) {
            $this->list[] = $this->memberRow();
        }
        while (count($this->list) > $count) {
            array_pop($this->list);
        }
    }

    protected function memberRow(array $overrides = []): array
    {
        return [
            ...$this->memberDefaults(),
            'id' => uniqid(),
            ...$overrides,
        ];
    }

    protected function memberDefaults(): array
    {
        return [
            'id' => null,
            'name' => '',
            'paternal_surname' => '',
            'maternal_surname' => '',
            'relationship' => '',
            'birth_date' => '',
            'curp' => '',
            'phone' => '',
            'occupation' => '',
            'marital_status' => '',
            'education_level' => '',
            'education_grade' => null,
            'weekly_income' => null,
            'origin_state' => '',
            'origin_country' => '',
            'religion' => '',
            'speaks_indigenous_language' => false,
            'indigenous_language' => '',
            'is_pregnant' => false,
            'pregnancy_months' => null,
            'medical_notes' => '',
            'is_land_owner' => false,
            'is_responsible' => false,
            'identification' => null,
            'birth_certificate' => null,
            'income_proof' => null,
        ];
    }

    public function getUploadRules(int $index): array
    {
        $member = $this->list[$index] ?? null;
        if (! $member) {
            return [];
        }

        return [
            "familyMembers.list.$index.identification" => 'nullable|mimes:jpg,jpeg,png,webp,heic,pdf|max:10240',
            "familyMembers.list.$index.birth_certificate" => 'nullable|mimes:jpg,jpeg,png,webp,heic,pdf|max:10240',
            "familyMembers.list.$index.income_proof" => 'nullable|mimes:jpg,jpeg,png,webp,heic,pdf|max:10240',
        ];
    }

    public function getUploadMessages(int $index): array
    {
        return [
            "familyMembers.list.$index.identification.mimes" => 'El archivo debe ser imagen o PDF.',
            "familyMembers.list.$index.birth_certificate.mimes" => 'El archivo debe ser imagen o PDF.',
            "familyMembers.list.$index.income_proof.mimes" => 'El comprobante debe ser imagen o PDF.',
        ];
    }

    public function getReviewRules(int $index): array
    {
        return [
            "familyMembers.list.$index.name" => 'required',
            "familyMembers.list.$index.paternal_surname" => 'required',
            "familyMembers.list.$index.maternal_surname" => 'required',
            "familyMembers.list.$index.relationship" => ['required', Rule::enum(Relationship::class)],
            "familyMembers.list.$index.birth_date" => 'required|date|before:today',
            "familyMembers.list.$index.curp" => [
                'nullable',
                'string',
                'size:18',
                'regex:/^[A-Z0-9]{18}$/i',
                new UniqueFamilyCurp($this->list, $index),
            ],
            "familyMembers.list.$index.phone" => ['nullable', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            "familyMembers.list.$index.education_level" => ['nullable', Rule::enum(EducationLevel::class)],
            "familyMembers.list.$index.education_grade" => 'nullable|integer|between:1,12',
            "familyMembers.list.$index.origin_country" => 'nullable|string|max:255',
            "familyMembers.list.$index.origin_state" => 'nullable|string|max:255',
            "familyMembers.list.$index.religion" => ['nullable', Rule::enum(Religion::class)],
            "familyMembers.list.$index.occupation" => ['nullable', Rule::enum(Occupation::class)],
            "familyMembers.list.$index.marital_status" => ['nullable', Rule::enum(MaritalStatus::class)],
            "familyMembers.list.$index.speaks_indigenous_language" => 'nullable|boolean',
            "familyMembers.list.$index.indigenous_language" => ['nullable', Rule::enum(IndigenousLanguage::class)],
            "familyMembers.list.$index.is_pregnant" => 'nullable|boolean',
            "familyMembers.list.$index.pregnancy_months" => 'nullable|integer|between:1,9',
            "familyMembers.list.$index.medical_notes" => 'nullable|string',
            "familyMembers.list.$index.is_land_owner" => 'nullable|boolean',
            "familyMembers.list.$index.is_responsible" => 'nullable|boolean',
            "familyMembers.list.$index.weekly_income" => 'nullable|numeric|min:0',
        ];
    }

    public function getReviewMessages(int $index): array
    {
        return [
            "familyMembers.list.$index.name.required" => 'El nombre es obligatorio.',
            "familyMembers.list.$index.paternal_surname.required" => 'El apellido paterno es obligatorio.',
            "familyMembers.list.$index.maternal_surname.required" => 'El apellido materno es obligatorio.',
            "familyMembers.list.$index.relationship.required" => 'El parentesco es obligatorio.',
            "familyMembers.list.$index.birth_date.required" => 'La fecha de nacimiento es obligatoria.',
            "familyMembers.list.$index.birth_date.before" => 'La fecha de nacimiento debe ser anterior a hoy.',
            "familyMembers.list.$index.curp.size" => 'El CURP debe tener exactamente 18 caracteres.',
            "familyMembers.list.$index.curp.regex" => 'El formato del CURP es inválido (solo letras y números sin espacios).',
            "familyMembers.list.$index.phone.regex" => 'El teléfono solo debe contener números, espacios o guiones.',
            "familyMembers.list.$index.education_grade.integer" => 'El grado cursado debe ser un número entero.',
            "familyMembers.list.$index.education_grade.between" => 'El año cursado debe ser entre 1 y 12.',
            "familyMembers.list.$index.pregnancy_months.integer" => 'Los meses de embarazo deben ser un número sin decimales.',
            "familyMembers.list.$index.pregnancy_months.between" => 'Los meses de embarazo deben ser entre 1 y 9.',
            "familyMembers.list.$index.weekly_income.numeric" => 'El ingreso semanal debe ser un número.',
            "familyMembers.list.$index.weekly_income.min" => 'El ingreso semanal no puede ser negativo.',
        ];
    }
}
