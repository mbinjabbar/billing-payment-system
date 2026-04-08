<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InsuranceFirm;
use Faker\Factory;

class InsuranceFirmSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();

        $firms = [
            ['name' => 'Aetna Health', 'type' => 'Health', 'code' => 'AET001'],
            ['name' => 'BlueCross BlueShield', 'type' => 'Health', 'code' => 'BCBS02'],
            ['name' => 'Cigna Medical', 'type' => 'Health', 'code' => 'CIG99'],
            ['name' => 'UnitedHealthcare', 'type' => 'Health', 'code' => 'UHC11'],
            ['name' => 'Medicare', 'type' => 'Health', 'code' => 'MEDICARE'],
            ['name' => 'Kaiser Permanente', 'type' => 'Health', 'code' => 'KP77'],
            ['name' => 'GEICO Auto Insurance', 'type' => 'Auto', 'code' => 'GEICO-NF'],
            ['name' => 'State Farm Mutual', 'type' => 'Auto', 'code' => 'SF-NF'],
            ['name' => 'Progressive Casualty', 'type' => 'Auto', 'code' => 'PROG-NF'],
        ];

        foreach ($firms as $firm) {
            InsuranceFirm::create(
                [
                    'carrier_code' => $firm['code'],
                    'name'           => $firm['name'],
                    'firm_type'      => $firm['type'],
                    'contact_person' => $faker->name,
                    'email'          => $faker->unique()->safeEmail,
                    'phone'          => $faker->phoneNumber,
                    'address'        => $faker->streetAddress . ', ' . $faker->city . ', ' . $faker->stateAbbr . ' ' . $faker->postcode,
                    'is_active'      => true,
                ]
            );
        }
    }
}
