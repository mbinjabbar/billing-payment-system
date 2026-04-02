<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;
use App\Models\Visit;
use \Faker\Factory;

class VisitSeeder extends Seeder
{

    public function run(): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 25; $i++) {
            Visit::create([
                'appointment_id'  => $i,
                'visit_date'      => $faker->dateTimeBetween('-5 days', 'now')->format('Y-m-d'),
                'visit_time'      => $faker->time('H:i:s'),
                'diagnosis'       => $faker->sentence(6),
                'treatment_notes' => $faker->paragraph(2),
                'prescriptions'   => $faker->boolean(80) ? $faker->sentence(10) : null,
                'follow_up_required' => $followUp = $faker->boolean(30),
                'follow_up_date' => $followUp ? $faker->dateTimeBetween('+3 days', '+14 days')->format('Y-m-d') : null,
                'status'          => $faker->randomElement(['Completed', 'Pending', 'Cancelled']),
            ]);
        }
    }
}
