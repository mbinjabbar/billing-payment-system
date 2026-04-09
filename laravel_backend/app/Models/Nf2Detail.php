<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nf2Detail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'case_id', 'policyholder_name', 'policy_number', 'claim_number',
        'accident_date', 'accident_time', 'accident_location',
        'accident_description', 'injury_description',
        'vehicle_owner_name', 'vehicle_make', 'vehicle_year', 'vehicle_type',
        'is_driver', 'is_passenger', 'is_pedestrian', 'is_household_member', 'is_relative_owner',
        'patient_ssn'
    ];

    public function patientCase()
    {
        return $this->belongsTo(PatientCase::class, 'case_id');
    }
}
