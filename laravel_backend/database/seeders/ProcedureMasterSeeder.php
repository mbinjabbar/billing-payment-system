<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcedureMaster;

class ProcedureMasterSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code' => '99213', 'name' => 'Office Visit, (Mid-Level)', 'charge' => 125.50],
            ['code' => '99214', 'name' => 'Office Visit, (Extended)', 'charge' => 210.00],
            ['code' => '71045', 'name' => 'Chest X-Ray', 'charge' => 85.00],
            ['code' => '73590', 'name' => 'Lower Limb X-Ray', 'charge' => 115.00],
            ['code' => '85025', 'name' => 'Complete Blood Count (CBC) Automated', 'charge' => 45.00],
            ['code' => '80048', 'name' => 'Basic Metabolic Panel', 'charge' => 75.00],
            ['code' => '70551', 'name' => 'Brain MRI without Contrast', 'charge' => 1450.00],
            ['code' => '72148', 'name' => 'Lumbar Spine MRI without Contrast', 'charge' => 1200.00],
            ['code' => '97161', 'name' => 'Physical Therapy Evaluation', 'charge' => 185.00],
            ['code' => '97110', 'name' => 'Therapeutic Exercise', 'charge' => 95.00],
            ['code' => '12001', 'name' => 'Simple Laceration Repair', 'charge' => 350.00],
            ['code' => '11400', 'name' => 'Excision of Benign Lesion', 'charge' => 650.00],
            ['code' => '99281', 'name' => 'Emergency Dept', 'charge' => 250.00],
            ['code' => '93000', 'name' => 'Electrocardiogram (ECG/EKG) Routine', 'charge' => 145.00],
            ['code' => '87804', 'name' => 'Influenza Rapid Antigen Test', 'charge' => 35.00],
            ['code' => '99203', 'name' => 'Initial Injury Evaluation', 'charge' => 185.00],
            ['code' => '72040', 'name' => 'Cervical Spine X-Ray', 'charge' => 95.00],
            ['code' => '72100', 'name' => 'Lumbosacral Spine X-Ray', 'charge' => 110.00],
            ['code' => '97140', 'name' => 'Manual Therapy', 'charge' => 75.00],
            ['code' => '98940', 'charge' => 65.00, 'name' => 'Chiropractic Manipulative Treatment'],
            ['code' => '97112', 'charge' => 85.00, 'name' => 'Neuromuscular Reeducation'],
            ['code' => '99215', 'charge' => 275.00, 'name' => 'High Complexity Injury Assessment'],
            ['code' => '95811', 'charge' => 950.00, 'name' => 'Sleep Study / Polysomnography'],
        ];

        foreach ($data as $p) {
            ProcedureMaster::create(
                [
                    'code'            => $p['code'],
                    'name'            => $p['name'],
                    'standard_charge' => $p['charge'],
                    'is_active'       => true
                ]
            );
        }
    }
}