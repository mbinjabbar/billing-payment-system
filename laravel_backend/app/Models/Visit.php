<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Appointment;
use App\Models\Bill;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    use softDeletes;
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
     public function appointment() {
        return $this->belongsTo(Appointment::class);
    }

    public function bill() {
        return $this->hasOne(Bill::class);
    }
}
