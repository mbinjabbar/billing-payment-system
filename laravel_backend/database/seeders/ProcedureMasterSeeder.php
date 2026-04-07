<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcedureMaster;

class ProcedureMasterSeeder extends Seeder
{
    public function run(): void
    {
$data = [
    ['99213', 'Office Visit, (Mid-Level)', 125.50],
    ['99214', 'Office Visit, (Extended)', 210.00],
    ['71045', 'Chest X-Ray', 85.00],
    ['73590', 'Lower Limb X-Ray', 115.00],
    ['85025', 'Complete Blood Count (CBC) Automated', 45.00],
    ['80048', 'Basic Metabolic Panel', 75.00],
    ['70551', 'Brain MRI without Contrast', 1450.00],
    ['72148', 'Lumbar Spine MRI without Contrast', 1200.00],
    ['97161', 'Physical Therapy Evaluation', 185.00],
    ['97110', 'Therapeutic Exercise', 95.00],
    ['12001', 'Simple Laceration Repair', 350.00],
    ['11400', 'Excision of Benign Lesion', 650.00],
    ['99281', 'Emergency Dept', 250.00],
    ['93000', 'Electrocardiogram (ECG/EKG) Routine', 145.00],
    ['87804', 'Influenza Rapid Antigen Test', 35.00],
    ['99203', 'Initial Injury Evaluation', 185.00],
    ['72040', 'Cervical Spine X-Ray', 95.00],
    ['72100', 'Lumbosacral Spine X-Ray', 110.00],
    ['97140', 'Manual Therapy', 75.00],
    ['98940', 'Chiropractic Manipulative Treatment', 65.00],
    ['97112', 'Neuromuscular Reeducation', 85.00],
    ['99215', 'High Complexity Injury Assessment', 275.00],
    ['95811', 'Sleep Study / Polysomnography', 950.00],
];

        foreach ($data as $p) {
            ProcedureMaster::create([
                'code'            => $p[0],
                'name'            => $p[1],
                'standard_charge' => $p[2],
            ]);
        }
    }
}
