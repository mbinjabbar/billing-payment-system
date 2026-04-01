<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();
        $roles = ['Admin', 'Biller', 'Payment Poster'];

        foreach ($roles as $role) {
            User::create([
                'first_name' => $faker->firstName,
                'last_name'  => $faker->lastName,
                'email'      => strtolower(str_replace(' ', '', $role)) . '@clinic.com',
                'password'   => bcrypt('password'),
                'role'       => $role,
            ]);
        }
    }
}
