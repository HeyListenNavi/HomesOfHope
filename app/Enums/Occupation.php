<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Occupation: string implements HasLabel
{
    case ConstructionWorker = 'construction_worker';
    case FactoryWorker = 'factory_worker';
    case HouseCleaner = 'house_cleaner';
    case Unemployed = 'unemployed';
    case SelfEmployed = 'self_employed';
    case Salesperson = 'salesperson';
    case CallCenterAgent = 'call_center_agent';
    case TaxiDriver = 'taxi_driver';
    case SecurityGuard = 'security_guard';
    case DeliveryDriver = 'delivery_driver';
    case RideshareDriver = 'rideshare_driver';
    case Waiter = 'waiter';
    case Cook = 'cook';
    case Retired = 'retired';
    case Housewife = 'housewife';
    case Student = 'student';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ConstructionWorker => 'Albañilería',
            self::FactoryWorker => 'Fábrica',
            self::HouseCleaner => 'Limpieza de casas',
            self::Unemployed => 'No trabaja',
            self::SelfEmployed => 'Autoempleado(a)',
            self::Salesperson => 'Vendedor(a)',
            self::CallCenterAgent => 'Call Center',
            self::TaxiDriver => 'Taxista',
            self::SecurityGuard => 'Guardia de seguridad',
            self::DeliveryDriver => 'Repartidor',
            self::RideshareDriver => 'Uber / Didi',
            self::Waiter => 'Mesero(a)',
            self::Cook => 'Cocinero(a)',
            self::Retired => 'Retirado(a)',
            self::Housewife => 'Ama de casa',
            self::Student => 'Estudiante',
            self::Other => 'Otro',
        };
    }
}
