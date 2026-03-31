<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'appointment_id',
        'visit_date',
        'visit_time',
        'diagnosis',
        'treatment_notes',
        'prescriptions',
        'follow_up_required',
        'follow_up_date',
        'status',
    ];
}
