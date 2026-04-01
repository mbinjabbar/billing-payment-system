<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patientcase extends Model
{
     use SoftDeletes;
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
       public function patient() {
        return $this->belongsTo(Patient::class);
    }

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }

   
}
