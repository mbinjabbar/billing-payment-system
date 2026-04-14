<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([
            ['key' => 'clinic_name',      'value' => 'MedBilling Clinic',       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'clinic_address',   'value' => '123 Medical Drive',        'created_at' => now(), 'updated_at' => now()],
            ['key' => 'clinic_phone',     'value' => '+1 (555) 000-0000',        'created_at' => now(), 'updated_at' => now()],
            ['key' => 'clinic_email',     'value' => 'billing@clinic.com',       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_tax_rate', 'value' => '0',                        'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_due_days', 'value' => '30',                       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'invoice_footer',   'value' => 'Thank you for your payment.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}