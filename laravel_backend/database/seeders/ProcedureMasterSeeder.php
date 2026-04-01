<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcedureMaster;

class ProcedureMasterSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['CONS-001', 'General Consultation', 125.50],
            ['CONS-002', 'Extended Specialist Visit', 210.00],
            ['XRAY-101', 'Chest X-Ray (Single View)', 85.00],
            ['XRAY-102', 'Lower Limb X-Ray', 115.00],
            ['LAB-201', 'Complete Blood Count (CBC)', 45.00],
            ['LAB-202', 'Metabolic Panel', 75.00],
            ['MRI-501', 'Brain MRI without Contrast', 1450.00],
            ['MRI-502', 'Lumbar Spine MRI', 1200.00],
            ['PT-001', 'Physical Therapy (Initial)', 185.00],
            ['PT-002', 'PT Session (Follow-up)', 95.00],
            ['SURG-901', 'Laceration Repair (Simple)', 350.00],
            ['SURG-902', 'Minor Cyst Removal', 650.00],
            ['ER-001', 'ER Triage Assessment', 250.00],
            ['EKG-001', 'Electrocardiogram', 145.00],
            ['FLU-001', 'Influenza Rapid Test', 35.00],
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
