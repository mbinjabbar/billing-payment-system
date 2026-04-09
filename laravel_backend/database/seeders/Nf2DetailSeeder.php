<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nf2Detail;
use App\Models\PatientCase;
use Faker\Factory as Faker;

class Nf2DetailSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $accidentCases = PatientCase::where('car_accident', true)->get();

        foreach ($accidentCases as $case) {
            Nf2Detail::create(
                [
                    'case_id' => $case->id,
                    'policyholder_name'    => $faker->name,
                    'policy_number'        => 'POL-' . strtoupper($faker->bothify('??###-####')),
                    'claim_number'         => 'CLM-' . strtoupper($faker->bothify('##??#-###')),
                    'accident_date'        => $case->opened_date ?? now()->subDays(5),
                    'accident_time'        => $faker->time('H:i'),
                    'accident_location'    => $faker->streetAddress . ', ' . $faker->city . ', NY',
                    'accident_description' => $faker->randomElement([
                        'Rear-end collision while stopped at a red light.',
                        'Side-impact collision at intersection due to failure to yield.',
                        'Single vehicle accident avoiding a deer on the highway.',
                        'Multiple vehicle pile-up due to icy road conditions.',
                        'Struck by another vehicle while merging onto the expressway.'
                    ]),
                    'injury_description'   => $faker->randomElement([
                        'Severe whiplash and lower back pain.',
                        'Fractured left radius and multiple contusions.',
                        'Concussion symptoms and cervical strain.',
                        'Knee trauma due to impact with dashboard.',
                        'Soft tissue damage in neck and shoulders.'
                    ]),
                    'vehicle_owner_name'   => $faker->name,
                    'vehicle_make'         => $faker->randomElement(['Toyota', 'Honda', 'Ford', 'Tesla', 'Chevrolet']),
                    'vehicle_year'         => $faker->year(),
                    'vehicle_type'         => $faker->randomElement(['Automobile', 'Truck', 'Motorcycle']),
                    'is_driver'            => $faker->boolean(70),
                    'is_passenger'         => $faker->boolean(20),
                    'is_pedestrian'        => $faker->boolean(5),
                    'is_household_member'  => $faker->boolean(10),
                    'is_relative_owner'    => $faker->boolean(5),
                    'patient_ssn'          => $faker->numerify('###-##-####'),
                ]
            );
        }
    }
}