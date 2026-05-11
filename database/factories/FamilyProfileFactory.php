<?php

namespace Database\Factories;

use App\Enums\ConditionLevel;
use App\Enums\Currency;
use App\Enums\HousingStatus;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyProfileFactory extends Factory
{
    protected $model = FamilyProfile::class;

    public function definition(): array
    {
        $familyName = fake()->lastName().' '.fake()->lastName();
        $housingStatus = fake()->randomElement(HousingStatus::cases());

        return [
            'family_name' => $familyName,
            'status' => fake()->randomElement(['new', 'approved', 'in_process', 'not_eligible', 'potential', 'built', 'dont_build']),
            'family_photo_path' => 'https://picsum.photos/200',
            'lives_on_land' => fake()->boolean(30),

            'home_city' => fake()->city(),
            'home_colony' => 'Colonia '.fake()->word(),
            'home_address' => fake()->streetAddress(),
            'home_address_link' => 'https://maps.google.com/?q='.urlencode(fake()->address()),

            'land_city' => fake()->city(),
            'land_colony' => 'Lomas de '.fake()->word(),
            'land_address' => 'Lote '.fake()->randomDigit().', Manzana '.fake()->randomDigit(),
            'land_address_link' => 'https://maps.google.com/?q='.urlencode(fake()->address()),

            'land_ownership_time' => fake()->numberBetween(1, 10).' años',
            'land_total_cost' => fake()->randomFloat(2, 50000, 200000),
            'land_down_payment' => fake()->randomFloat(2, 5000, 20000),
            'land_monthly_payment' => fake()->randomFloat(2, 1000, 5000),
            'land_currency' => fake()->randomElement(Currency::cases()),
            'land_last_payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'land_is_up_to_date' => fake()->boolean(80),
            'land_is_flat' => fake()->boolean(50),
            'land_services' => fake()->randomElements(['electricity', 'water', 'septic_tank', 'sewage'], rand(1, 4)),

            'home_status' => $housingStatus,
            'home_ownership_time' => fake()->numberBetween(1, 15).' años',
            'home_owner_name' => in_array($housingStatus->value, ['rented', 'borrowed']) ? fake()->name() : null,
            'home_monthly_rent' => $housingStatus->value === 'rented' ? fake()->numberBetween(1500, 5000) : null,
            'home_monthly_rent_currency' => $housingStatus->value === 'rented' ? fake()->randomElement(Currency::cases()) : Currency::MXN,
            'home_has_receipts' => $housingStatus->value === 'rented' ? fake()->boolean() : false,

            'home_roof_material' => fake()->randomElement(['Lámina', 'Madera', 'Concreto']),
            'home_roof_condition' => fake()->randomElement(ConditionLevel::cases()),
            'home_floor_material' => fake()->randomElement(['Tierra', 'Cemento', 'Mosaico']),
            'home_floor_condition' => fake()->randomElement(ConditionLevel::cases()),
            'home_walls_material' => fake()->randomElement(['Madera', 'Bloque', 'Adobe', 'Lámina']),
            'home_walls_condition' => fake()->randomElement(ConditionLevel::cases()),

            'home_bedrooms_count' => fake()->numberBetween(1, 4),
            'home_bedrooms_description' => fake()->sentence(),
            'home_bathroom_location' => fake()->randomElement(['inside', 'outside']),
            'home_bathroom_description' => fake()->sentence(),
            'home_furniture_owned' => fake()->boolean(),
            'home_furniture_description' => fake()->sentence(),

            'opened_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'closed_at' => fake()->boolean(20) ? fake()->dateTimeBetween('now', '+1 year') : null,
            'has_addictions' => $hasAddictions = fake()->boolean(20),
            'addictions_details' => $hasAddictions ? fake()->randomElement(['Alcoholismo', 'Drogadicción', 'Tabaquismo severo', 'Ludopatía']) : null,
            'general_observations' => fake()->paragraph(),
            'responsible_member_id' => FamilyMember::where('is_responsible', true)->inRandomOrder()->first(),
        ];
    }
}
