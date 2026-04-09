<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientCase extends Model
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
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits()
    {
        return $this->hasManyThrough(Visit::class, Appointment::class);
    }

    public function nf2Detail()
    {
        return $this->hasOne(Nf2Detail::class);
    }
}
