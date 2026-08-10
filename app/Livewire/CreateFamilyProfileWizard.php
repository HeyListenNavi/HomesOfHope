<?php

namespace App\Livewire;

use App\Livewire\Forms\DocumentsForm;
use App\Livewire\Forms\FamilyForm;
use App\Livewire\Forms\FamilyMembersForm;
use App\Livewire\Forms\HomeForm;
use App\Livewire\Forms\LandForm;
use App\Models\Applicant;
use App\Models\FamilyMember;
use App\Services\FamilyProfile\FamilyProfileService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CreateFamilyProfileWizard extends Component
{
    use WithFileUploads;

    public Applicant $applicant;

    public bool $notEligible = false;

    public bool $alreadyCompleted = false;

    public int $step = 1;

    public FamilyForm $family;

    public LandForm $land;

    public HomeForm $home;

    public FamilyMembersForm $familyMembers;

    public DocumentsForm $docs;

    public function mount(Applicant $applicant)
    {
        $this->applicant = $applicant;

        if (! $applicant->group_id) {
            $this->notEligible = true;

            return;
        }

        $this->familyMembers->prefillSelfMember($applicant);

        if ($applicant->completed_at) {
            $this->step = $this->totalSteps + 1;

            return;
        }

        if ($this->membersHaveExistingCurp()) {
            $this->alreadyCompleted = true;
        }
    }

    public function updated($property, $value)
    {
        if ($property === 'family.member_count') {
            $this->familyMembers->syncCount((int) $value);
        }
        if ($property === 'docs.new_land_receipts') {
            $this->docs->syncNewReceipts();
        }
    }

    #[Computed]
    public function flowSteps(): array
    {
        $steps = [];
        $steps[] = ['type' => 'family'];
        $steps[] = ['type' => 'land'];
        if ($this->family->lives_on_land === false) {
            $steps[] = ['type' => 'home'];
        }

        for ($i = 0; $i < $this->family->member_count; $i++) {
            $steps[] = ['type' => 'member_upload', 'index' => $i];
            $steps[] = ['type' => 'member_review', 'index' => $i];
        }

        $steps[] = ['type' => 'general_docs'];

        return $steps;
    }

    #[Computed]
    public function totalSteps(): int
    {
        return count($this->flowSteps());
    }

    #[Computed]
    public function currentStep(): ?array
    {
        $flowSteps = $this->flowSteps();

        return $this->step <= count($flowSteps) ? $flowSteps[$this->step - 1] : null;
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->step < $this->totalSteps) {
            $this->step++;
            $this->scrollToTop();
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
            $this->scrollToTop();
        }
    }

    public function submit(FamilyProfileService $service)
    {
        $this->validateAllSteps();

        if ($this->applicant->completed_at) {
            $this->step = $this->totalSteps + 1;

            return;
        }

        if ($this->membersHaveExistingCurp()) {
            $this->alreadyCompleted = true;

            return;
        }

        $service->storeFamilyProfile(
            $this->applicant,
            $this->family,
            $this->land,
            $this->home,
            $this->familyMembers,
            $this->docs
        );

        $this->step = $this->totalSteps + 1;
        $this->scrollToTop();
    }

    protected function validateCurrentStep(): void
    {
        try {
            $this->validateStepDefinition($this->flowSteps()[$this->step - 1]);
        } catch (ValidationException $e) {
            $this->scrollToTop();
            throw $e;
        }
    }

    protected function validateAllSteps(): void
    {
        try {
            foreach ($this->flowSteps() as $stepDefinition) {
                $this->validateStepDefinition($stepDefinition);
            }
        } catch (ValidationException $e) {
            $this->scrollToTop();
            throw $e;
        }
    }

    protected function validateStepDefinition(array $stepDefinition): void
    {
        switch ($stepDefinition['type']) {
            case 'family':
                $this->family->validate();
                break;
            case 'land':
                $this->land->validate();
                break;
            case 'home':
                $this->home->validate();
                break;
            case 'member_upload':
                $this->validate(
                    $this->familyMembers->getUploadRules($stepDefinition['index']),
                    $this->familyMembers->getUploadMessages($stepDefinition['index'])
                );
                break;
            case 'member_review':
                $this->validate(
                    $this->familyMembers->getReviewRules($stepDefinition['index']),
                    $this->familyMembers->getReviewMessages($stepDefinition['index'])
                );
                break;
            default:
                $this->validate(
                    $this->docs->resolveRules((bool) $this->family->parents_married),
                    $this->docs->resolveMessages((bool) $this->family->parents_married)
                );
        }
    }

    protected function membersHaveExistingCurp(): bool
    {
        $curps = collect($this->familyMembers->list)
            ->pluck('curp')
            ->filter()
            ->map(fn (string $curp) => Str::upper(trim($curp)))
            ->unique()
            ->values();

        if ($curps->isEmpty()) {
            return false;
        }

        return FamilyMember::query()->whereIn('curp', $curps)->exists();
    }

    public function removeLandReceipt(int $index): void
    {
        $this->docs->removeLandReceipt($index);
    }

    protected function scrollToTop(): void
    {
        $this->dispatch('scroll-to-top');
    }

    public function render()
    {
        return view('livewire.create-family-profile-wizard')
            ->with(['title' => 'Crear Perfil Familiar']);
    }
}
