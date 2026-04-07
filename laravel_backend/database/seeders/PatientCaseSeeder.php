<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PatientCase;
use \Faker\Factory;

class PatientCaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 25; $i++) {
            PatientCase::create([
                'patient_id'    => $i,
                'case_number'   => 'CASE-' . now()->year . '-' . $faker->unique()->numberBetween(100, 999),
                'case_type'     => $faker->randomElement(['New', 'Follow-up', 'Emergency', 'Consultation','Surgical', 'Chronic']),
                'case_category' => $faker->randomElement(['Cardiology', 'Orthopedics', 'General Medicine', 'Neurology', 'Pediatrics', 'Dermatology', 'Gynecology', 'Ophthalmology', 'ENT', 'Dental', 'Psychiatry', 'Other']),
                'priority'      => $faker->randomElement(['Low', 'Normal', 'High', 'Urgent']),
                'status'        => $faker->randomElement(['Active', 'Closed', 'Transferred', 'On Hold']),
                'description'   => $faker->paragraph(1),
                'opened_date' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                'referring_doctor' => $faker->boolean(40) ? 'Dr. ' . $faker->lastName : null,
                'car_accident' => $faker->boolean(),
            ]);
        }
    }
}
