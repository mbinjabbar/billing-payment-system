<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PatientCase;
use App\Models\Visit;
use Illuminate\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\HasOne;

class Appointment extends Model
{ 
    use SoftDeletes;
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
    public function case() {
        return $this->belongsTo(PatientCase::class);
    }

    public function visit() {
        return $this->hasOne(Visit::class);
    }
}
