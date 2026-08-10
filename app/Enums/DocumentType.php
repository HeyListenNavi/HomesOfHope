<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DocumentType: string implements HasColor, HasLabel
{
    case Identification = 'identification';
    case BirthCertificate = 'birth_certificate';
    case IncomeProof = 'income_proof';
    case MarriageCertificate = 'marriage_certificate';
    case FamilyPhoto = 'family_photo';
    case LandOwnership = 'land_ownership';
    case LandReceipt = 'land_receipt';

    case Ine = 'ine';
    case Curp = 'curp';
    case ProofOfAddress = 'proof_of_address';
    case Contract = 'contract';
    case Report = 'report';
    case Photo = 'photo';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Identification => '🆔 Identificación (INE u otro)',
            self::BirthCertificate => '📄 Acta de Nacimiento',
            self::IncomeProof => '💰 Comprobante de Salario',
            self::MarriageCertificate => '💍 Acta de Matrimonio',
            self::FamilyPhoto => '📷 Foto Familiar',
            self::LandOwnership => '✍️ Contrato o Título del Terreno',
            self::LandReceipt => '🧾 Recibo de Mensualidad',
            self::Ine => '🆔 INE',
            self::Curp => '📄 CURP',
            self::ProofOfAddress => '🏠 Comprobante Domicilio',
            self::Contract => '✍️ Contrato',
            self::Report => '📊 Reporte / Estudio',
            self::Photo => '📷 Fotografía',
            self::Other => '📂 Otro',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Identification, self::BirthCertificate, self::IncomeProof, self::ProofOfAddress => 'info',
            self::MarriageCertificate, self::LandReceipt => 'warning',
            self::FamilyPhoto, self::Photo => 'primary',
            self::LandOwnership, self::Contract => 'success',
            self::Report => 'warning',
            self::Curp => 'info',
            self::Other => 'gray',
            self::Ine => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->getLabel()])
            ->all();
    }
}
