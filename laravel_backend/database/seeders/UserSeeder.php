<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();
        $roles = UserRole::ALL_ROLES;

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
