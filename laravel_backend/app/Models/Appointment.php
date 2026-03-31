<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{ 
    protected $fillable = [
        'patientcase_id',
        'appointment_type',
        'appointment_status',
        'appointment_date',
        'appointment_time',
        'duration_minutes',
        'doctor_id',
        'doctor_name',
        'notes',
        'reminder_sent',
    ];
}
