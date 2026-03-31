<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patientcase extends Model
{
    protected $fillable = [
        'patient_id',
        'case_number',
        'case_type',
        'case_category',
        'priority',
        'status',
        'description',
        'opened_date',
        'closed_date',
        'referring_doctor',
    ];


   
}
