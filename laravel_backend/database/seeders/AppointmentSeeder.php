<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\PatientCase;
use Faker\Factory;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();
        $caseIds = PatientCase::pluck('id')->toArray();

        for ($i = 1; $i <= 100; $i++) {
            Appointment::create([
                'patient_case_id' => $faker->randomElement($caseIds),
                'appointment_type'   => $faker->randomElement(['Initial', 'Follow-up', 'Consultation', 'Procedure', 'Telehealth', 'Emergency', 'Routine Checkup']),
                'appointment_status' => 'Completed',
                'appointment_date'   => $faker->dateTimeBetween('-10 days', '-2 days')->format('Y-m-d'),
                'appointment_time'   => $faker->time('H:i:s'),
                'duration_minutes'   => $faker->randomElement([15, 30, 45, 60]),
                'doctor_name'        => 'Dr. ' . $faker->name,
                'specialty_required' => $faker->randomElement(['Cardiology', 'Orthopedics', 'General Medicine', null]),
                'notes'              => $faker->sentence(10),
                'reminder_sent'      => $faker->boolean(50),
            ]);
        }
    }
}
