<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PatientCase;
use Illuminate\Database\Eloquent\relations\HasMany;

class Patient extends Model
{ 
    use SoftDeletes;
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];
    public function cases() {
        return $this->hasMany(PatientCase::class);
    }
}
