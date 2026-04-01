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
        $firms = ['Aetna', 'BlueCross', 'Cigna', 'UnitedHealth', 'Medicare', 'Kaiser', 'MetLife', 'Geico'];

        foreach ($firms as $name) {
            InsuranceFirm::create([
                'name'           => $name . ' ' . $faker->companySuffix,
                'contact_person' => $faker->name,
                'email'          => $faker->companyEmail,
                'phone'          => $faker->phoneNumber,
            ]);
        }
    }
}
