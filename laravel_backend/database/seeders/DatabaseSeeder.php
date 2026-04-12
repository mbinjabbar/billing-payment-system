<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            InsuranceFirmSeeder::class,
            ProcedureMasterSeeder::class, 
            PatientSeeder::class,
            PatientCaseSeeder::class,
            AppointmentSeeder::class,
            VisitSeeder::class,
            Nf2DetailSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
