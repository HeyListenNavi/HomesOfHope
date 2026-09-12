<?php

namespace App\Services\FamilyProfile;

use App\Enums\DocumentType;
use App\Enums\FamilyStatus;
use App\Jobs\ProcessMemberOcrJob;
use App\Livewire\Forms\DocumentsForm;
use App\Livewire\Forms\FamilyForm;
use App\Livewire\Forms\FamilyMembersForm;
use App\Livewire\Forms\HomeForm;
use App\Livewire\Forms\LandForm;
use App\Models\Applicant;
use App\Models\Document;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use App\Services\Applicant\ApplicantService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FamilyProfileService
{
    public function storeFamilyProfile(
        Applicant $applicant,
        FamilyForm $family,
        LandForm $land,
        HomeForm $home,
        FamilyMembersForm $members,
        DocumentsForm $docs
    ): void {
        DB::transaction(function () use ($applicant, $family, $land, $home, $members, $docs) {
            $familyPhotoPath = $docs->family_photo
                ? $docs->family_photo->store('documents', 'r2')
                : null;

            $homeData = $family->lives_on_land ? [] : [
                'home_city' => $home->city,
                'home_colony' => $home->colony,
                'home_address' => $home->address,
                'home_address_link' => $this->buildMapLink($home->lat, $home->lng),
                'home_latitude' => $home->lat,
                'home_longitude' => $home->lng,
                'home_status' => $home->status,
                'home_ownership_time' => $home->ownership_time,
                'home_owner_name' => $home->owner_name,
                'home_monthly_rent' => $home->monthly_rent,
                'home_monthly_rent_currency' => $home->monthly_rent_currency ?: 'mxn',
                'home_has_receipts' => $home->has_receipts,
                'house_description' => $home->description,
            ];

            $profileData = [
                'family_name' => $family->name,
                'slug' => Str::slug($family->name.'-'.uniqid()),
                'status' => FamilyStatus::PreProfile,
                'lives_on_land' => $family->lives_on_land,
                'family_photo_path' => $familyPhotoPath,
                'opened_at' => null,
                'has_addictions' => $family->has_addictions,
                'addictions_details' => $family->addictions_details,
            ];

            $landData = [
                'land_city' => $land->city,
                'land_colony' => $land->colony,
                'land_address' => $land->address,
                'land_address_link' => $this->buildMapLink($land->lat, $land->lng),
                'land_latitude' => $land->lat,
                'land_longitude' => $land->lng,
                'land_ownership_time' => $land->ownership_time,
                'land_total_cost' => $land->total_cost,
                'land_down_payment' => $land->down_payment,
                'land_monthly_payment' => $land->monthly_payment,
                'land_currency' => $land->currency ?: 'mxn',
                'land_last_payment_date' => $land->last_payment_date,
                'land_is_up_to_date' => $land->is_up_to_date,
                'land_is_flat' => $land->is_flat,
                'land_services' => $land->services,
            ];

            $profile = FamilyProfile::create(array_merge($profileData, $homeData, $landData));

            $responsibleMemberId = null;
            foreach ($members->list as $memberData) {
                $memberData = collect($memberData)->map(fn ($value) => $value === '' ? null : $value)->all();

                $member = FamilyMember::create([
                    'family_profile_id' => $profile->id,
                    'name' => $memberData['name'],
                    'paternal_surname' => $memberData['paternal_surname'],
                    'maternal_surname' => $memberData['maternal_surname'],
                    'relationship' => $memberData['relationship'],
                    'birth_date' => $memberData['birth_date'],
                    'curp' => $memberData['curp'],
                    'phone' => $memberData['phone'],
                    'occupation' => $memberData['occupation'],
                    'marital_status' => $memberData['marital_status'],
                    'education_level' => $memberData['education_level'],
                    'education_grade' => $memberData['education_grade'],
                    'weekly_income' => $memberData['weekly_income'],
                    'origin_state' => $memberData['origin_state'],
                    'origin_country' => $memberData['origin_country'],
                    'religion' => $memberData['religion'],
                    'speaks_indigenous_language' => $memberData['speaks_indigenous_language'] ?? false,
                    'indigenous_language' => $memberData['indigenous_language'],
                    'is_pregnant' => $memberData['is_pregnant'] ?? false,
                    'pregnancy_months' => $memberData['pregnancy_months'],
                    'medical_notes' => $memberData['medical_notes'],
                    'is_responsible' => $memberData['is_responsible'] ?? false,
                    'is_land_owner' => $memberData['is_land_owner'] ?? false,
                ]);

                if (! empty($memberData['is_responsible'])) {
                    $responsibleMemberId = $member->id;
                }

                if (! empty($memberData['identification_doc'])) {
                    $this->createDocument($member, DocumentType::Identification, $memberData['identification_doc']);
                }

                if (! empty($memberData['income_proof'])) {
                    $this->createDocument($member, DocumentType::IncomeProof, $memberData['income_proof']);
                }
            }

            $profile->update(['responsible_member_id' => $responsibleMemberId]);

            if ($family->parents_married && $docs->marriage_certificate) {
                $this->createDocument($profile, DocumentType::MarriageCertificate, $docs->marriage_certificate);
            }

            if ($docs->family_photo) {
                Document::create([
                    'documentable_type' => FamilyProfile::class,
                    'documentable_id' => $profile->id,
                    'document_type' => DocumentType::FamilyPhoto->value,
                    'original_name' => $docs->family_photo->getClientOriginalName(),
                    'file_path' => $familyPhotoPath,
                    'mime_type' => $docs->family_photo->getMimeType(),
                    'size' => $docs->family_photo->getSize(),
                ]);
            }

            if ($docs->land_ownership) {
                $this->createDocument($profile, DocumentType::LandOwnership, $docs->land_ownership);
            }

            foreach ($docs->land_receipts as $receipt) {
                if ($receipt) {
                    $this->createDocument($profile, DocumentType::LandReceipt, $receipt);
                }
            }

            $applicant->update([
                'completed_at' => now(),
            ]);

            $this->dispatchOcrJobs($profile, $applicant);
        });
    }

    private function dispatchOcrJobs(FamilyProfile $profile, Applicant $applicant): void
    {
        $memberJobs = $profile->members()
            ->whereHas('documents', fn ($q) => $q->where('document_type', 'identification'))
            ->get()
            ->map(fn (FamilyMember $member) => new ProcessMemberOcrJob($member))
            ->all();

        $sendNotification = function () use ($applicant, $profile) {
            $link = URL::temporarySignedRoute(
                'applicant.complete-profile',
                now()->addDays(7),
                ['familyProfile' => $profile->id]
            );

            app(ApplicantService::class)->sendCompleteProfileNotification($applicant, $link);
        };

        if (! empty($memberJobs)) {
            Bus::batch($memberJobs)
                ->then($sendNotification)
                ->catch($sendNotification)
                ->allowFailures()
                ->dispatch();

            return;
        }

        $sendNotification();
    }

    private function createDocument(Model $model, DocumentType $type, ?TemporaryUploadedFile $file): void
    {
        if (! $file) {
            return;
        }

        $path = $file->store('documents', 'r2');

        Document::create([
            'documentable_type' => $model::class,
            'documentable_id' => $model->id,
            'document_type' => $type->value,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    private function buildMapLink($lat, $lng): ?string
    {
        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.urlencode("{$lat},{$lng}");
    }
}
