<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use Faker\Factory;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 25; $i++) {
            $gender = $faker->randomElement(['Male', 'Female', 'Other']);
            Patient::create([
                'first_name'              => $faker->firstName($gender),
                'middle_name'             => $faker->boolean(70) ? $faker->firstName : null,
                'last_name'               => $faker->lastName,
                'email'                   => $faker->unique()->safeEmail,
                'phone'                   => $faker->phoneNumber,
                'mobile'                  => $faker->phoneNumber,
                'date_of_birth'           => $faker->date('Y-m-d', '2005-01-01'),
                'gender'                  => $gender,
                'address'                 => $faker->streetAddress,
                'city'                    => $faker->city,
                'state'                   => $faker->stateAbbr,
                'postal_code'             => $faker->postcode,
                'country'                 => 'USA',
                'emergency_contact_name'  => $faker->name,
                'emergency_contact_phone' => $faker->phoneNumber,
            ]);
        }
    }
}
