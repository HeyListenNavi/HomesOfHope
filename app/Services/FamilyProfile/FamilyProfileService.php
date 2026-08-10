<?php

namespace App\Services\FamilyProfile;

use App\Enums\DocumentType;
use App\Enums\FamilyStatus;
use App\Livewire\Forms\DocumentsForm;
use App\Livewire\Forms\FamilyForm;
use App\Livewire\Forms\FamilyMembersForm;
use App\Livewire\Forms\HomeForm;
use App\Livewire\Forms\LandForm;
use App\Models\Applicant;
use App\Models\Document;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use Illuminate\Support\Facades\DB;
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

            $profile = FamilyProfile::create([
                'family_name' => $family->name,
                'slug' => Str::slug($family->name.'-'.uniqid()),
                'status' => FamilyStatus::New,
                'lives_on_land' => $family->lives_on_land,
                'family_photo_path' => $familyPhotoPath,
                'opened_at' => null,

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

                'home_city' => $family->lives_on_land ? null : $home->city,
                'home_colony' => $family->lives_on_land ? null : $home->colony,
                'home_address' => $family->lives_on_land ? null : $home->address,
                'home_address_link' => $family->lives_on_land ? null : $this->buildMapLink($home->lat, $home->lng),
                'home_latitude' => $family->lives_on_land ? null : $home->lat,
                'home_longitude' => $family->lives_on_land ? null : $home->lng,
                'home_status' => $family->lives_on_land ? null : $home->status,
                'home_ownership_time' => $family->lives_on_land ? null : $home->ownership_time,
                'home_owner_name' => $family->lives_on_land ? null : $home->owner_name,
                'home_monthly_rent' => $family->lives_on_land ? null : $home->monthly_rent,
                'home_monthly_rent_currency' => $home->monthly_rent_currency ?: 'mxn',
                'home_has_receipts' => $family->lives_on_land ? null : $home->has_receipts,
                'house_description' => $family->lives_on_land ? null : $home->description,

                'has_addictions' => $family->has_addictions,
                'addictions_details' => $family->addictions_details,
            ]);

            $responsibleMemberId = null;
            foreach ($members->list as $memberData) {
                $memberData = collect($memberData)->map(fn ($value) => $value === '' ? null : $value)->all();

                $member = FamilyMember::create([
                    'family_profile_id' => $profile->id,
                    'name' => $memberData['name'],
                    'paternal_surname' => $memberData['paternal_surname'],
                    'maternal_surname' => $memberData['maternal_surname'],
                    'relationship' => $memberData['relationship'],
                    'birth_date' => $memberData['birth_date'] ?: null,
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
                    'speaks_indigenous_language' => (bool) ($memberData['speaks_indigenous_language'] ?? false),
                    'indigenous_language' => $memberData['indigenous_language'],
                    'is_pregnant' => (bool) ($memberData['is_pregnant'] ?? false),
                    'pregnancy_months' => $memberData['pregnancy_months'],
                    'medical_notes' => $memberData['medical_notes'],
                    'is_responsible' => (bool) ($memberData['is_responsible'] ?? false),
                    'is_land_owner' => (bool) ($memberData['is_land_owner'] ?? false),
                ]);

                if (! empty($memberData['is_responsible'])) {
                    $responsibleMemberId = $member->id;
                }

                if (! empty($memberData['identification'])) {
                    $this->createDocument($member, DocumentType::Identification, $memberData['identification']);
                }
                if (! empty($memberData['birth_certificate'])) {
                    $this->createDocument($member, DocumentType::BirthCertificate, $memberData['birth_certificate']);
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
                $this->createDocument($profile, DocumentType::FamilyPhoto, $docs->family_photo);
            }
            if ($docs->land_ownership) {
                $this->createDocument($profile, DocumentType::LandOwnership, $docs->land_ownership);
            }
            foreach ($docs->land_receipts as $receipt) {
                $this->createDocument($profile, DocumentType::LandReceipt, $receipt);
            }

            $applicant->update([
                'completed_at' => now(),
            ]);
        });
    }

    private function createDocument($model, DocumentType $type, TemporaryUploadedFile $file): void
    {
        $path = $file->store('documents', 'r2');

        Document::create([
            'documentable_type' => get_class($model),
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
