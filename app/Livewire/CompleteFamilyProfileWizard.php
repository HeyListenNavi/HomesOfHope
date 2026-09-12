<?php

namespace App\Livewire;

use App\Enums\EducationLevel;
use App\Enums\IndigenousLanguage;
use App\Enums\MaritalStatus;
use App\Enums\Occupation;
use App\Enums\Relationship;
use App\Enums\Religion;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CompleteFamilyProfileWizard extends Component
{
    public FamilyProfile $profile;

    public int $step = 1;

    public array $members = [];

    public bool $notFound = false;

    public bool $alreadyCompleted = false;

    public function mount(FamilyProfile $familyProfile)
    {
        $this->profile = $familyProfile;

        if ($familyProfile->completed_at) {
            $this->alreadyCompleted = true;

            return;
        }

        $this->members = $familyProfile->members()->get()->map(fn (FamilyMember $member) => [
            'id' => $member->id,
            'name' => $member->name,
            'paternal_surname' => $member->paternal_surname,
            'maternal_surname' => $member->maternal_surname,
            'relationship' => $member->relationship,
            'birth_date' => $member->birth_date,
            'curp' => $member->curp,
            'phone' => $member->phone,
            'occupation' => $member->occupation,
            'marital_status' => $member->marital_status,
            'education_level' => $member->education_level,
            'education_grade' => $member->education_grade,
            'weekly_income' => $member->weekly_income,
            'origin_state' => $member->origin_state,
            'origin_country' => $member->origin_country,
            'religion' => $member->religion,
            'speaks_indigenous_language' => $member->speaks_indigenous_language,
            'indigenous_language' => $member->indigenous_language,
            'is_pregnant' => $member->is_pregnant,
            'pregnancy_months' => $member->pregnancy_months,
            'medical_notes' => $member->medical_notes,
            'is_land_owner' => $member->is_land_owner,
            'is_responsible' => $member->is_responsible,
        ])->toArray();
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->step < $this->getTotalSteps()) {
            $this->step++;
            $this->scrollToTop();
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->scrollToTop();
        }
    }

    public function submit(): void
    {
        $this->validateAllSteps();

        foreach ($this->members as $memberData) {
            FamilyMember::where('id', $memberData['id'])->update([
                'name' => $memberData['name'],
                'paternal_surname' => $memberData['paternal_surname'],
                'maternal_surname' => $memberData['maternal_surname'],
                'relationship' => $memberData['relationship'],
                'birth_date' => $memberData['birth_date'] ?: null,
                'curp' => $memberData['curp'] ?? null,
                'phone' => $memberData['phone'] ?? null,
                'occupation' => $memberData['occupation'] ?? null,
                'marital_status' => $memberData['marital_status'] ?? null,
                'education_level' => $memberData['education_level'] ?? null,
                'education_grade' => $memberData['education_grade'] ?? null,
                'weekly_income' => $memberData['weekly_income'] ?? null,
                'origin_state' => $memberData['origin_state'] ?? null,
                'origin_country' => $memberData['origin_country'] ?? null,
                'religion' => $memberData['religion'] ?? null,
                'speaks_indigenous_language' => (bool) ($memberData['speaks_indigenous_language'] ?? false),
                'indigenous_language' => $memberData['indigenous_language'] ?? null,
                'is_pregnant' => (bool) ($memberData['is_pregnant'] ?? false),
                'pregnancy_months' => $memberData['pregnancy_months'] ?? null,
                'medical_notes' => $memberData['medical_notes'] ?? null,
                'is_land_owner' => (bool) ($memberData['is_land_owner'] ?? false),
                'is_responsible' => (bool) ($memberData['is_responsible'] ?? false),
            ]);
        }

        $this->profile->update(['completed_at' => now()]);
        $this->alreadyCompleted = true;
        $this->scrollToTop();
    }

    public function getTotalSteps(): int
    {
        return count($this->members);
    }

    public function getCurrentMember(): ?array
    {
        if ($this->step <= count($this->members)) {
            return $this->members[$this->step - 1];
        }

        return null;
    }

    protected function validateCurrentStep(): void
    {
        try {
            $memberIndex = $this->step - 1;
            $this->validate($this->getMemberRules($memberIndex), $this->getMemberMessages($memberIndex));
        } catch (ValidationException $e) {
            $this->scrollToTop();
            throw $e;
        }
    }

    protected function validateAllSteps(): void
    {
        try {
            foreach ($this->members as $index => $member) {
                $this->validate($this->getMemberRules($index), $this->getMemberMessages($index));
            }
        } catch (ValidationException $e) {
            $this->scrollToTop();
            throw $e;
        }
    }

    protected function getMemberRules(int $index): array
    {
        return [
            "members.{$index}.name" => 'required',
            "members.{$index}.paternal_surname" => 'required',
            "members.{$index}.maternal_surname" => 'required',
            "members.{$index}.relationship" => ['required', Rule::enum(Relationship::class)],
            "members.{$index}.birth_date" => 'required|date|before:today',
            "members.{$index}.curp" => ['nullable', 'string', 'size:18', 'regex:/^[A-Z0-9]{18}$/i'],
            "members.{$index}.phone" => ['nullable', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            "members.{$index}.education_level" => ['nullable', Rule::enum(EducationLevel::class)],
            "members.{$index}.education_grade" => 'nullable|integer|between:1,12',
            "members.{$index}.origin_country" => 'nullable|string|max:255',
            "members.{$index}.origin_state" => 'nullable|string|max:255',
            "members.{$index}.religion" => ['nullable', Rule::enum(Religion::class)],
            "members.{$index}.occupation" => ['nullable', Rule::enum(Occupation::class)],
            "members.{$index}.marital_status" => ['nullable', Rule::enum(MaritalStatus::class)],
            "members.{$index}.speaks_indigenous_language" => 'nullable|boolean',
            "members.{$index}.indigenous_language" => ['nullable', Rule::enum(IndigenousLanguage::class)],
            "members.{$index}.is_pregnant" => 'nullable|boolean',
            "members.{$index}.pregnancy_months" => 'nullable|integer|between:1,9',
            "members.{$index}.medical_notes" => 'nullable|string',
            "members.{$index}.is_land_owner" => 'nullable|boolean',
            "members.{$index}.is_responsible" => 'nullable|boolean',
            "members.{$index}.weekly_income" => 'nullable|numeric|min:0',
        ];
    }

    protected function getMemberMessages(int $index): array
    {
        return [
            "members.{$index}.name.required" => 'El nombre es obligatorio.',
            "members.{$index}.paternal_surname.required" => 'El apellido paterno es obligatorio.',
            "members.{$index}.maternal_surname.required" => 'El apellido materno es obligatorio.',
            "members.{$index}.relationship.required" => 'El parentesco es obligatorio.',
            "members.{$index}.birth_date.required" => 'La fecha de nacimiento es obligatoria.',
            "members.{$index}.birth_date.before" => 'La fecha de nacimiento debe ser anterior a hoy.',
            "members.{$index}.curp.size" => 'El CURP debe tener exactamente 18 caracteres.',
            "members.{$index}.curp.regex" => 'El formato del CURP es inválido.',
            "members.{$index}.phone.regex" => 'El teléfono solo debe contener números, espacios o guiones.',
        ];
    }

    protected function scrollToTop(): void
    {
        $this->dispatch('scroll-to-top');
    }

    public function render()
    {
        return view('livewire.complete-family-profile-wizard')
            ->with(['title' => 'Completar Perfil Familiar']);
    }
}
